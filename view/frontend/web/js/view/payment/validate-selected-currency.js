define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Coinpayments_CoinPayments/js/model/validate-selected-currency'
    ],
    function (
        Component,
        additionalValidators,
        validateSelectedCurrency
    ) {
        'use strict';
        additionalValidators.registerValidator(validateSelectedCurrency);
        return Component.extend({});
    }
);