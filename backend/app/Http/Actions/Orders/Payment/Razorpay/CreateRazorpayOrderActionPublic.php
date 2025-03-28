<?php

namespace HiEvents\Http\Actions\Orders\Payment\Razorpay;

use HiEvents\Exceptions\Razorpay\CreateRazorpayOrderFailedException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Handlers\Order\Payment\Razorpay\CreateRazorpayOrderHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateRazorpayOrderActionPublic extends BaseAction
{
    private CreateRazorpayOrderHandler $createRazorpayOrderHandler;

    public function __construct(CreateRazorpayOrderHandler $createRazorpayOrderHandler)
    {
        $this->createRazorpayOrderHandler = $createRazorpayOrderHandler;
    }

    public function __invoke(int $eventId, string $orderShortId): JsonResponse
    {
        try {
            $razorpayOrder = $this->createRazorpayOrderHandler->handle($orderShortId);
        } catch (CreateRazorpayOrderFailedException $e) {
            \Log::error('Razorpay Order Creation Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->jsonResponse([
            'key_id' => config('services.razorpay.key_id'),
            'order_id' => $razorpayOrder->orderId,
            'amount' => $razorpayOrder->amount,
            'currency' => $razorpayOrder->currency,
            'name' => $razorpayOrder->eventName,
            'description' => $razorpayOrder->description
        ]);
    }
}