<?php

namespace HiEvents\Services\Domain\Payment\Razorpay\EventHandlers;

use HiEvents\DomainObjects\Status\OrderPaymentStatus;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\RazorpayPaymentsRepositoryInterface;
use Psr\Log\LoggerInterface;

readonly class PaymentFailedHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private RazorpayPaymentsRepositoryInterface $razorpayPaymentsRepository,
        private LoggerInterface $logger,
    )
    {
    }

    public function handlePayment($payment): void
    {
        $razorpayPayment = $this->razorpayPaymentsRepository->findByRazorpayOrderId($payment->order_id);

        if (!$razorpayPayment) {
            $this->logger->error('Razorpay payment not found', [
                'razorpay_order_id' => $payment->order_id,
            ]);
            return;
        }

        // Update order status to failed
        $this->orderRepository->updateWhere(
            attributes: [
                'payment_status' => OrderPaymentStatus::PAYMENT_FAILED->name,
            ],
            where: [
                'id' => $razorpayPayment->getOrderId(),
            ]
        );

        $this->logger->info('Payment failed', [
            'razorpay_order_id' => $payment->order_id,
            'error' => $payment->error_description ?? 'Unknown error'
        ]);
    }
}