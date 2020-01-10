define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        console.log('method-renderer/coin-method');

        rendererList.push(
            {
                type: 'coin_payments',
                component: 'Coinpayments_CoinPayments/js/view/payment/method-renderer/coin-method'
            }
        );
        return Component.extend({});
    }
);