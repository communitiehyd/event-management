import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { LoadingMask } from "../../../common/LoadingMask";
import { CheckoutContent } from "../../../layouts/Checkout/CheckoutContent";
import { t } from "@lingui/macro";
import { eventHomepagePath } from "../../../../utilites/urlHelper.ts";
import { useGetEventPublic } from "../../../../queries/useGetEventPublic.ts";
import { HomepageInfoMessage } from "../../../common/HomepageInfoMessage";
import { useGetOrderPublic } from "../../../../queries/useGetOrderPublic";
import { orderClientPublic } from "../../../../api/order.client";

const RazorpayPayment = () => {
    const { eventId, orderShortId } = useParams();
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const { data: event } = useGetEventPublic(eventId);
    const { data: order } = useGetOrderPublic(eventId, orderShortId);

    const handlePayment = async () => {
        try {
            setIsLoading(true);
            const response = await orderClientPublic.createRazorpayOrder(
                Number(eventId),
                orderShortId!,
                localStorage.getItem(`order_session_${orderShortId}`) || ''
            );

            const options = {
                key: response.key_id,
                amount: response.amount,
                currency: response.currency,
                name: event?.title,
                description: `Order #${orderShortId}`,
                order_id: response.order_id,
                prefill: {
                    name: `${order?.first_name} ${order?.last_name}`,
                    email: order?.email,
                },
                handler: async function(response: any) {
                    try {
                        await orderClientPublic.verifyRazorpayPayment(
                            Number(eventId), 
                            orderShortId!, 
                            {
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature
                            }
                        );
                        window.location.href = `/checkout/${eventId}/${orderShortId}/summary`;
                    } catch (error) {
                        setError('Payment verification failed');
                    }
                }
            };

            const rzp = new (window as any).Razorpay(options);
            rzp.on('payment.failed', function (response: any) {
                setError(`Payment failed: ${response.error.description}`);
                // Redirect to payment page with error
                window.location.href = `/checkout/${eventId}/${orderShortId}/payment?payment_failed=true`;
            });
            rzp.open();
        } catch (error) {
            setError('Failed to initialize payment');
        } finally {
            setIsLoading(false);
        }
    };

    if (error) {
        return (
            <CheckoutContent>
                <HomepageInfoMessage
                    message={error}
                    link={eventHomepagePath(event!)}
                    linkText={t`Return to event page`}
                />
            </CheckoutContent>
        );
    }

    return (
        <CheckoutContent>
            {isLoading ? (
                <LoadingMask />
            ) : (
                <div>
                    <h2>Complete Your Payment</h2>
                    <button onClick={handlePayment}>
                        Pay Now
                    </button>
                </div>
            )}
        </CheckoutContent>
    );
};

export default RazorpayPayment;