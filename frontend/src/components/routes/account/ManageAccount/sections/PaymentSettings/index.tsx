import {t} from "@lingui/macro";
import {Card} from "../../../../../common/Card";
import {HeadingCard} from "../../../../../common/HeadingCard";
import {useCreateOrGetStripeConnectDetails} from "../../../../../../queries/useCreateOrGetStripeConnectDetails.ts";
import {useGetAccount} from "../../../../../../queries/useGetAccount.ts";
import {LoadingMask} from "../../../../../common/LoadingMask";
import {Anchor, Button, Grid, Group, Text, ThemeIcon, Title} from "@mantine/core";
import {Account} from "../../../../../../types.ts";
import paymentClasses from "./PaymentSettings.module.scss";
import classes from "../../ManageAccount.module.scss";
import {useEffect, useState} from "react";
import {IconAlertCircle, IconBrandStripe, IconCheck, IconExternalLink} from '@tabler/icons-react';
import {formatCurrency} from "../../../../../../utilites/currency.ts";
import {showSuccess} from "../../../../../../utilites/notifications.tsx";

interface FeePlanDisplayProps {
    configuration?: {
        name: string;
        application_fees: {
            percentage: number;
            fixed: number;
        };
        is_system_default: boolean;
    };
}

const formatPercentage = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'percent',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value / 100);
};

const FeePlanDisplay = ({configuration}: FeePlanDisplayProps) => {
    if (!configuration) return null;

    return (
        <div className={paymentClasses.stripeInfo}>
            <Title mb={10} order={3}>{t`Platform Fees`}</Title>

            <Text size="sm" c="dimmed" mb="lg">
                {t`Hi.Events charges platform fees to maintain and improve our services. These fees are automatically deducted from each transaction.`}
            </Text>

            <Card variant={'lightGray'}>
                <Title order={4}>{configuration.name}</Title>
                <Grid>
                    {configuration.application_fees.percentage > 0 && (
                        <Grid.Col span={{base: 12, sm: 6}}>
                            <Group gap="xs" wrap={'nowrap'}>
                                <Text size="sm">
                                    {t`Transaction Fee:`}{' '}
                                    <Text span fw={600}>
                                        {formatPercentage(configuration.application_fees.percentage)}
                                    </Text>
                                </Text>
                            </Group>
                        </Grid.Col>
                    )}
                    {configuration.application_fees.fixed > 0 && (
                        <Grid.Col span={{base: 12, sm: 6}}>
                            <Group gap="xs" wrap={'nowrap'}>
                                <Text size="sm">
                                    {t`Fixed Fee:`}{' '}
                                    <Text span fw={600}>
                                        {formatCurrency(configuration.application_fees.fixed)}
                                    </Text>
                                </Text>
                            </Group>
                        </Grid.Col>
                    )}
                </Grid>
            </Card>

            <Text size="xs" c="dimmed" mt="md">
                <Group gap="xs" align="center" wrap={'nowrap'}>
                    <IconAlertCircle size={14}/>
                    <Text
                        span>{t`Fees are subject to change. You will be notified of any changes to your fee structure.`}</Text>
                </Group>
            </Text>
        </div>
    );
};

const ConnectStatus = ({account}: { account: Account }) => {
    const [fetchStripeDetails, setFetchStripeDetails] = useState(false);
    const [isReturningFromStripe, setIsReturningFromStripe] = useState(false);

    useEffect(() => {
        // if (typeof window === 'undefined') {
        //     return;
        // }
        // setIsReturningFromStripe(
        //     window.location.search.includes('is_return') || window.location.search.includes('is_refresh')
        // );
    }, []);

    return (
        <div className={paymentClasses.stripeInfo}>
            {/* {props.stripeDetails?.is_connect_setup_complete && (
                <>
                    <h2>{t`You have connected your Stripe account`}</h2>
                    <p>
                        {t`You can now start receiving payments through Stripe.`}
                    </p>
                </>
            )} */}
            { 
            // !props.stripeDetails?.is_connect_setup_complete
             // &&
               (
                <>
                    <h2>
                        {!isReturningFromStripe && t`You have not connected your Stripe account`}
                        {isReturningFromStripe && t`You have not completed your Stripe Connect setup`}
                    </h2>
                    <p>
                        {t`We use Stripe to process payments. Connect your Stripe account to start receiving payments.`}
                    </p>
                    <p>
                        <Group gap={20}>
                            <Button variant={'light'}
                                    onClick={() => {
                                        if (typeof window !== 'undefined')
                                            window.location.href = String(props.stripeDetails?.connect_url);
                                    }}
                            >
                                {(!isReturningFromStripe) && t`Connect Stripe`}
                                {(isReturningFromStripe) && t`Continue Stripe Connect Setup`}
                            </Button>
                            <Anchor target={'_blank'} href={'https://stripe.com/'}>
                                {t`Learn more about Stripe`}
                            </Anchor>
                        </Group>
                    </Group>
                </>
            )}
        </div>
    );
};

const PaymentSettings = () => {
    const accountQuery = useGetAccount();
    const stripeDetailsQuery = useCreateOrGetStripeConnectDetails(accountQuery.data?.id);
    const stripeDetails =   {   account: "",
        stripe_account_id: "",
        is_connect_setup_complete: false,
        connect_url: ""};
    //stripeDetailsQuery.data;
    const error = stripeDetailsQuery.error as any;


    // if (error?.response?.status === 403) {
    //     return (
    //         <>
    //             <Card className={classes.tabContent}>
    //                 <div className={paymentClasses.stripeInfo}>
    //                     <h2>{t`You do not have permission to access this page`}</h2>
    //                     <p>
    //                         {error?.response?.data?.message}
    //                     </p>
    //                 </div>
    //             </Card>
    //         </>
    //     );
    // }

    return (
        <>
            <HeadingCard
                heading={t`Payment Settings`}
                subHeading={t`Manage your payment processing and view platform fees`}
            />
            <Card className={classes.tabContent}>
                <LoadingMask/>
                {
                //stripeDetails &&
                 <ConnectStatus stripeDetails={stripeDetails}/>
                 }
            </Card>
        </>
    );
};

export default PaymentSettings;
