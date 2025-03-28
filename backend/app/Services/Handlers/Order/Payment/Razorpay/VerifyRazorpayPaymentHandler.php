<?php

namespace HiEvents\Services\Handlers\Order\Payment\Razorpay;

use HiEvents\DomainObjects\RazorpayPaymentDomainObject;
use HiEvents\DomainObjects\Status\OrderPaymentStatus;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Services\Domain\Payment\Razorpay\EventHandlers\PaymentSucceededHandler;
use Psr\Log\LoggerInterface;
use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

readonly class VerifyRazorpayPaymentHandler
{
    public function __construct(
        private RazorpayApi $razorpay,
        private OrderRepositoryInterface $orderRepository,
        private LoggerInterface $logger,
        private PaymentSucceededHandler $paymentSucceededHandler,
    )
    {
    }

    public function handle(string $orderId, string $razorpayPaymentId, string $razorpayOrderId, string $razorpaySignature): void
    {
        $order = $this->orderRepository
            ->loadRelation(new Relationship(
                domainObject: RazorpayPaymentDomainObject::class,
                name: 'razorpay_payment',
            ))
            ->findByShortId($orderId);

        if (!$order || !$order->getRazorpayPayment()) {
            throw new ResourceNotFoundException('Order or payment not found');
        }

        try {
            // Verify the payment signature
            $attributes = [
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature
            ];

            $this->razorpay->utility->verifyPaymentSignature($attributes);

            // If payment is already marked as received, no need to process again
            if ($order->getPaymentStatus() === OrderPaymentStatus::PAYMENT_RECEIVED->name) {
                return;
            }

            // Fetch payment details
            $payment = $this->razorpay->payment->fetch($razorpayPaymentId);

            // Process successful payment
            $this->paymentSucceededHandler->handlePayment($payment);

        } catch (SignatureVerificationError $e) {
            $this->logger->error('Razorpay signature verification failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->getId(),
                'order_short_id' => $order->getShortId(),
                'razorpay_payment_id' => $razorpayPaymentId,
            ]);

            throw new \Exception('Payment verification failed');
        } catch (\Exception $e) {
            $this->logger->error('Failed to verify Razorpay payment', [
                'error' => $e->getMessage(),
                'order_id' => $order->getId(),
                'order_short_id' => $order->getShortId(),
                'razorpay_payment_id' => $razorpayPaymentId,
            ]);

            throw $e;
        }
    }
}