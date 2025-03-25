import {useQuery} from "@tanstack/react-query";
import {orderClientPublic} from "../api/order.client.ts";
import {IdParam, Order} from "../types.ts";

export const GET_ORDER_PUBLIC_QUERY_KEY = 'getOrderPublic';

export const useGetOrderPublic = (eventId: IdParam, orderShortId: IdParam, includes: string[] = []) => {
    console.log('Query Params:', { eventId, orderShortId, includes });
    return useQuery<Order>({
        queryKey: [GET_ORDER_PUBLIC_QUERY_KEY, eventId, orderShortId],

        queryFn: async () => {
            const {data} = await orderClientPublic.findBySessionShortId(
                Number(eventId),
                String(orderShortId),
                includes,
            );
            console.log('Query Response:', data);
            return data;
        },

        refetchOnWindowFocus: false,
        staleTime: 0,
        retryOnMount: false,
        retry: false
    });
}
