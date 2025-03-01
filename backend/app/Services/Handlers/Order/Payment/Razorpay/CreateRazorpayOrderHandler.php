<?php

namespace HiEvents\Services\Handlers\Order\Payment\Razorpay;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use HiEvents\DomainObjects\Generated\RazorpayPaymentDomainObjectAbstract;
use HiEvents\DomainObjects\OrderItemDomainObject;
use HiEvents\DomainObjects\RazorpayPaymentDomainObject;
use HiEvents\Exceptions\Razorpay\CreateRazorpayOrderFailedException;
use HiEvents\Exceptions\UnauthorizedException;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\RazorpayPaymentsRepositoryInterface;
use HiEvents\Services\Domain\Payment\Razorpay\DTOs\CreateRazorpayOrderResponseDTO;
use HiEvents\Services\Infrastructure\Session\CheckoutSessionManagementService;
use HiEvents\Repository\Eloquent\Value\Relationship as ValueRelationship;
use HiEvents\DomainObjects\EventDomainObject;
use Razorpay\Api\Api as RazorpayApi;

readonly class CreateRazorpayOrderHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private RazorpayApi $razorpay,
        private CheckoutSessionManagementService $sessionIdentifierService,
        private RazorpayPaymentsRepositoryInterface $razorpayPaymentsRepository
    )
    {
    }

    public function handle(string $orderShortId): CreateRazorpayOrderResponseDTO
    {
        $order = $this->orderRepository
        ->loadRelation(new Relationship(OrderItemDomainObject::class))
        ->loadRelation(new Relationship(RazorpayPaymentDomainObject::class, name: 'razorpay_payment'))
        ->loadRelation(new ValueRelationship(EventDomainObject::class, name: 'event'))  // Add 'name' parameter
        ->findByShortId($orderShortId);


            // Now you can access event title
        $eventTitle = $order->getEvent()->getTitle();
        // if (!$order || !$this->sessionIdentifierService->verifySession($order->getSessionId())) {
        //     throw new UnauthorizedException(__('Sorry, we could not verify your session. Please create a new order.'));
        // }

        // If we already have a Razorpay order then return it
        if ($order->getRazorpayPayment() !== null) {
            $existingOrder = $this->razorpay->order->fetch($order->getRazorpayPayment()->getRazorpayOrderId());
            return new CreateRazorpayOrderResponseDTO(
                orderId: $existingOrder->id,
                amount: $existingOrder->amount,
                currency: $existingOrder->currency,
                eventName: $order->getEvent()->getTitle(),
                description: "Order #{$order->getShortId()}"
            );
        }

        try {
            // Create Razorpay order
            $razorpayOrder = $this->razorpay->order->create([
                'amount' => Money::of($order->getTotalGross(), $order->getCurrency())->getMinorAmount()->toInt(),
                'currency' => $order->getCurrency(),
                'receipt' => $order->getShortId(),
                'notes' => [
                    'order_id' => $order->getId(),
                    'event_id' => $order->getEventId(),
                    'order_short_id' => $order->getShortId()
                ]
            ]);

            // Save order details
            $this->razorpayPaymentsRepository->create([
                RazorpayPaymentDomainObjectAbstract::ORDER_ID => $order->getId(),
                RazorpayPaymentDomainObjectAbstract::RAZORPAY_ORDER_ID => $razorpayOrder->id,
            ]);

            return new CreateRazorpayOrderResponseDTO(
                orderId: $razorpayOrder->id,
                amount: $razorpayOrder->amount,
                currency: $razorpayOrder->currency,
                eventName: $order->getEvent()->getTitle(),
                description: "Order #{$order->getShortId()}"
            );

        } catch (\Exception $e) {
            throw new CreateRazorpayOrderFailedException($e->getMessage());
        }
    }
}