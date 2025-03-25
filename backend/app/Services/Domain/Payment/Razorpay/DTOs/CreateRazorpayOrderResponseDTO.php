<?php

namespace HiEvents\Services\Domain\Payment\Razorpay\DTOs;

readonly class CreateRazorpayOrderResponseDTO
{
    public function __construct(
        public ?string $orderId = null,
        public ?string $keyId = null,
        public ?int $amount = null,
        public ?string $currency = null,
        public ?string $error = null,
        public ?string $eventName = null,    // Add this
        public ?string $description = null   // Add this
    )
    {
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'key_id' => $this->keyId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'error' => $this->error,
            'event_name' => $this->eventName,
            'description' => $this->description
        ];
    }
}