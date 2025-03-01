<?php

namespace HiEvents\Models;

class RazorpayPayment extends BaseModel
{
    protected function getCastMap(): array
    {
        return [
            'last_error' => 'array',
        ];
    }

    protected function getFillableFields(): array
    {
        return [
            'order_id',
            'razorpay_order_id',
            'razorpay_payment_id',
            'razorpay_signature',
            'amount_received',
            'merchant_id'
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}