<?php

namespace HiEvents\Repository\Interfaces;

use HiEvents\DomainObjects\Interfaces\DomainObjectInterface;

interface RazorpayPaymentsRepositoryInterface extends RepositoryInterface
{
    // Method signature must match parent interface
    public function create(array $attributes): DomainObjectInterface;
    public function findByOrderId(int $orderId);
    public function findByRazorpayOrderId(string $razorpayOrderId);
}