<?php

namespace HiEvents\Http\Actions\Orders\Payment\Razorpay;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Handlers\Order\Payment\Razorpay\VerifyRazorpayPaymentHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRazorpayPaymentActionPublic extends BaseAction
{
    private VerifyRazorpayPaymentHandler $verifyPaymentHandler;

    public function __construct(VerifyRazorpayPaymentHandler $verifyPaymentHandler)
    {
        $this->verifyPaymentHandler = $verifyPaymentHandler;
    }

    public function __invoke(Request $request, int $eventId, string $orderShortId): JsonResponse
    {
        try {
            $this->verifyPaymentHandler->handle(
                orderId: $orderShortId,
                razorpayPaymentId: $request->input('razorpay_payment_id'),
                razorpayOrderId: $request->input('razorpay_order_id'),
                razorpaySignature: $request->input('razorpay_signature')
            );

            return $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}