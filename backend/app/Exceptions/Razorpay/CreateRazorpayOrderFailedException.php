<?php

namespace HiEvents\Exceptions\Razorpay;

use Exception;

class CreateRazorpayOrderFailedException extends Exception
{
    public function __construct(string $message = "Failed to create Razorpay order")
    {
        parent::__construct($message);
    }
}