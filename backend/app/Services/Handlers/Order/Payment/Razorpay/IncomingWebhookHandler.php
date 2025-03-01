<?php

namespace HiEvents\Services\Handlers\Order\Payment\Razorpay;

use HiEvents\Exceptions\CannotAcceptPaymentException;
use HiEvents\Services\Domain\Payment\Razorpay\EventHandlers\PaymentSucceededHandler;
use HiEvents\Services\Domain\Payment\Razorpay\EventHandlers\PaymentFailedHandler;
use HiEvents\Services\Domain\Payment\Razorpay\EventHandlers\RefundProcessedHandler;
use HiEvents\Services\Handlers\Order\Payment\Razorpay\DTO\RazorpayWebhookDTO;
use Illuminate\Log\Logger;
use JsonException;
use Razorpay\Api\Api as RazorpayApi;
use Throwable;

readonly class IncomingWebhookHandler
{
    public function __construct(
        private RazorpayApi $razorpay,
        private PaymentSucceededHandler $paymentSucceededHandler,
        private PaymentFailedHandler $paymentFailedHandler,
        private RefundProcessedHandler $refundProcessedHandler,
        private Logger $logger
    )
    {
    }

    /**
     * @throws JsonException
     * @throws Throwable
     */
    public function handle(RazorpayWebhookDTO $webhookDTO): void
    {
        try {
            // Verify webhook signature
            $this->razorpay->utility->verifyWebhookSignature(
                $webhookDTO->payload,
                $webhookDTO->signature,
                config('services.razorpay.webhook_secret')
            );

            $payload = json_decode($webhookDTO->payload, true, 512, JSON_THROW_ON_ERROR);
            $event = $payload['event'];

            $this->logger->debug('Razorpay event received', $payload);

            // Handle different webhook events
            switch ($event) {
                case 'payment.captured':
                    $this->paymentSucceededHandler->handlePayment($payload['payload']['payment']['entity']);
                    break;
                case 'payment.failed':
                    $this->paymentFailedHandler->handlePayment($payload['payload']['payment']['entity']);
                    break;
                case 'refund.processed':
                    $this->refundProcessedHandler->handleRefund($payload['payload']['refund']['entity']);
                    break;
                default:
                    $this->logger->debug(sprintf('Unhandled Razorpay webhook: %s', $event));
            }
        } catch (CannotAcceptPaymentException $exception) {
            $this->logger->error(
                'Cannot accept payment: ' . $exception->getMessage(), [
                    'payload' => $webhookDTO->payload,
                ]
            );
            throw $exception;
        } catch (Throwable $exception) {
            $this->logger->error('Unhandled Razorpay error: ' . $exception->getMessage(), [
                'payload' => $webhookDTO->payload,
            ]);
            throw $exception;
        }
    }
}