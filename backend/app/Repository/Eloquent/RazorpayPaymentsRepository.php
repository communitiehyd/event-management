<?php

namespace HiEvents\Repository\Eloquent;

use HiEvents\Models\RazorpayPayment;
use HiEvents\DomainObjects\Interfaces\DomainObjectInterface;
use HiEvents\Repository\Interfaces\RazorpayPaymentsRepositoryInterface;
use HiEvents\DomainObjects\RazorpayPaymentDomainObject;

class RazorpayPaymentsRepository extends BaseRepository implements RazorpayPaymentsRepositoryInterface
{
    protected function getModel(): string
    {
        return RazorpayPayment::class;
    }

    public function getDomainObject(): string
    {
        return RazorpayPaymentDomainObject::class;
    }

    public function create(array $attributes): DomainObjectInterface
    {
        return parent::create($attributes);
    }

    public function findByOrderId(int $orderId)
    {
        return $this->findFirstWhere([
            'order_id' => $orderId
        ]);
    }

    public function findByRazorpayOrderId(string $razorpayOrderId)
    {
        return $this->findFirstWhere([
            'razorpay_order_id' => $razorpayOrderId
        ]);
    }
}