define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/totals',
    ],
    function (ko,
              Component,
              quote,
              $,
              placeOrderAction,
              selectPaymentMethodAction,
              customer,
              checkoutData,
              additionalValidators,
              url,
              fullScreenLoader,
              errorProcessor,
              totals
    ) {
        'use strict';

        window.fullScreenLoader = fullScreenLoader;

        return Component.extend({
            defaults: {
                template: 'Coinpayments_CoinPayments/payment/coin_payment'
            },
            /**
             * @returns {string}
             */
            getCode: function () {
                return 'coin_payments';
            },
            afterPlaceOrder: function () {
                this.createInvoice(
                    window.checkoutConfig.payment.coinpayments.currentData.currency,
                    window.checkoutConfig.payment.coinpayments.currentData.total,
                    url.build('checkout/onepage/success')
                );
            },
            getCaption: function () {
                var caption = 'Select Coinpayments.Net currency';
                if (window.checkoutConfig.payment.coinpayments.currencies.error) {
                    caption = window.checkoutConfig.payment.coinpayments.currencies.error.name;
                }
                return caption;
            },
            getCurrencies: function () {
                if (!window.checkoutConfig.payment.coinpayments.currencies.error) {
                    return ko.observableArray(window.checkoutConfig.payment.coinpayments.currencies);
                }
                return [];
            },
            /**
             *
             * @returns {boolean}
             */
            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },
            /**
             *
             * @param data
             * @param event
             * @returns {boolean}
             */
            placeOrder: function (data, event) {

                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },
            getBaseGrandTotal: function () {
                if (totals.totals()) {
                    var grandTotal = parseFloat(totals.totals()['grand_total']);
                    return grandTotal;
                }
                return window.checkoutConfig.totalsData.base_grand_total;
            },
            getPaymentAcceptanceMarkSrc: function () {
                return window.checkoutConfig.payment.coinpayments.logo;
            },
            getCurrencyCode: function () {
                return window.checkoutConfig.totalsData.quote_currency_code;
            },
            /**
             *
             * @param element
             * @param event
             */
            getConvertedAmount: function (element, event) {
                var elemToChange = $('#converted_amount_coinpayments');
                var coinCurrencyId = $(event.target).find('option:selected').val();
                var coinCurrencyCode = false;
                var coinCurrencyPrecision = false;

                window.checkoutConfig.payment.coinpayments.currencies.forEach(function (val, index) {
                    if (val.body.currencyId == coinCurrencyId) {
                        coinCurrencyCode = val.body.symbol;
                        coinCurrencyPrecision = val.body.decimalPlaces;
                    }
                })

                if (coinCurrencyId) {
                    var url = '/rest/V1/coinpayments/currency/set';
                    var data = {
                        coinCurrencyId: coinCurrencyId,
                        coinCurrencyPrecision: coinCurrencyPrecision,
                    };
                    $.ajax({
                        type: "POST",
                        encoding: 'UTF-8',
                        url: url,
                        showLoader: true,
                        contentType: "application/json",
                        data: JSON.stringify(data),
                        dataType: 'json',
                        success: function (result) {
                            result = JSON.parse(result);
                            var total = result.converted_amount;
                            total = total.toFixed(coinCurrencyPrecision);
                            elemToChange.val(total + ' ' + coinCurrencyCode);
                            window.checkoutConfig.payment.coinpayments.currentData = {
                                total: total,
                                currency: coinCurrencyCode
                            };
                        },
                        error: function (err) {
                            //TODO error logic
                        }
                    });
                }
            },
            /**
             * @returns {boolean}
             */
            getAllowPlaceOrder: function () {
                return this.getCode() === this.isChecked();
            },
            /**
             *
             * @param currency
             * @param value
             * @param redirect
             */
            createInvoice: function (currency, value, redirect) {

                var url = '/rest/V1/coinpayments/invoice/create';

                $.ajax({
                    type: "POST",
                    url: url,
                    contentType: "application/json",
                    success: function (result) {
                        var invoiceId = result[0];

                        try {
                            window.popup = undefined;
                            showPaymentsPopup(invoiceId, redirect);
                        } catch (e) {
                            console.error(e);
                        }

                    },
                    error: function (err) {
                        console.log(error);
                        if (redirect) {
                            window.location.replace(redirect);
                        }
                    }
                });
            }
        })
            ;
    }
)
;


function showPaymentsPopup(invoiceId, redirect) {

    window.fullScreenLoader.startLoader();
    var apiBaseUrl = "https://orion-api-testnet.starhermit.com";
    var id = "Checkout_Magento2_" + (Math.random() * 9007199254740991).toString(16);
    var checkoutAppUrl = apiBaseUrl + "/checkout";

    var popupWidth = 480 + 20;
    var popupHeight = 620 + 60;

    var popupFeatures = {
        width: popupWidth,
        height: popupHeight,
        status: 1,
        toolbar: 0,
        menubar: 0,
        resizable: 1,
        scrollbars: 1
    };
    var features = Object.keys(popupFeatures)
        .map(function (key) {
            return key + "=" + popupFeatures[key];
        })
        .join(",");

    window.popup = window.open(checkoutAppUrl, id, features);

    var interval = setInterval(function () {
        if (!window.popup || window.popup.closed) {
            clearInterval(interval);
            window.fullScreenLoader.stopLoader(true);
            window.popup = undefined;
            if (redirect) {
                window.location.replace(redirect);
            }
        }
    }, 1000);

    window.addEventListener("message", function (event) {
        switch (event.data.action) {
            case "CoinPaymentsCheckoutAppInitialized":
                var msg = {
                    action: "CoinPaymentsCheckoutAppInitializeInvoice",
                    data: {
                        apiBaseUrl: apiBaseUrl,
                        invoiceId: invoiceId
                    }
                };
                window.popup.postMessage(msg, "*");
                return false;
            case "CoinPaymentsPaymentButtonModalClosePressed":
            case "CoinPaymentsCheckoutAppInvoiceCancelled":
            case "CoinPaymentsCheckoutAppInvoiceConfirmed":
                window.fullScreenLoader.stopLoader(true);
                popup.close();
                if (redirect) {
                    window.location.replace(redirect);
                }
                return false;
        }
    }, true);

    window.popup;
}