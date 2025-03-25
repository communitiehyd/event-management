<?php

namespace HiEvents\Services\Domain\Payment\Razorpay\EventHandlers;

use HiEvents\DomainObjects\Generated\RazorpayPaymentDomainObjectAbstract;
use HiEvents\DomainObjects\Status\OrderPaymentStatus;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\RazorpayPaymentsRepositoryInterface;
use HiEvents\Services\Domain\Ticket\TicketQuantityUpdateService;
use Psr\Log\LoggerInterface;

readonly class PaymentSucceededHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private RazorpayPaymentsRepositoryInterface $razorpayPaymentsRepository,
        private TicketQuantityUpdateService     $quantityUpdateService,
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

        $order = $this->orderRepository->findById($razorpayPayment->getOrderId());

        if (!$order) {
            $this->logger->error('Order not found for Razorpay payment', [
                'razorpay_order_id' => $payment->order_id,
                'order_id' => $razorpayPayment->getOrderId(),
            ]);
            return;
        }

        // Update payment details
        $this->razorpayPaymentsRepository->updateWhere(
            attributes: [
                RazorpayPaymentDomainObjectAbstract::RAZORPAY_PAYMENT_ID => $payment->id,
                RazorpayPaymentDomainObjectAbstract::AMOUNT_RECEIVED => $payment->amount,
            ],
            where: [
                RazorpayPaymentDomainObjectAbstract::RAZORPAY_ORDER_ID => $payment->order_id,
            ]
        );

        // Update order status
        $this->orderRepository->updateWhere(
            attributes: [
                'status' => OrderStatus::COMPLETED->name,
                'payment_status' => OrderPaymentStatus::PAYMENT_RECEIVED->name,
            ],
            where: [
                'id' => $order->getId(),
            ]
        );
        $this->quantityUpdateService->updateQuantitiesFromOrder($order);

        $this->logger->info('Payment succeeded', [
            'razorpay_order_id' => $payment->order_id,
            'order_id' => $order->getId(),
            'amount' => $payment->amount,
        ]);
    }
}