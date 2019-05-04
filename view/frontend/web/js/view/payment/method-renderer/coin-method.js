/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

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
        'Coinpayments_CoinPayments/js/action/set-payment-method',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/error-processor'
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
              setPaymentMethodAction,
              fullScreenLoader,
              errorProcessor) {
        'use strict';
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
            /**
             * @returns {boolean}
             */
            isActive: function () {
                return true;
            },
            afterPlaceOrder: function () {
                // fullScreenLoader.startLoader();
                var self = this;
                if (self.getIsDirect()) {
                    self.createTransaction(
                        window.checkoutConfig.payment.coinpayments.currentData.currency,
                        window.checkoutConfig.payment.coinpayments.currentData.total,
                        url.build('coinpayments/transaction/status/')
                    );
                } else {
                    window.location.replace(url.build('coinpayments/invoice/index/'));
                }
            },
            getRedirectionText: function () {

                var iframeHtml;
                jQuery.ajax({
                    url: url.build('coinpayments/iframe/index/'),
                    async: false,
                    dataType: "json",
                    success: function (a) {
                        iframeHtml = a.html;
                    }

                });
                return iframeHtml;
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
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            getCurrencies: function () {
                if (!window.checkoutConfig.payment.coinpayments.available_currencies.error) {
                    return window.checkoutConfig.payment.coinpayments.available_currencies;
                }
                return [{error: "error"}];
            },
            getAcceptedCurrencies: function () {
                if (!window.checkoutConfig.payment.coinpayments.accepted_currencies.error) {
                    return window.checkoutConfig.payment.coinpayments.accepted_currencies;
                }
                return [{error: "error"}];
            },
            getPaymentAcceptanceMarkSrc: function () {
                return window.checkoutConfig.payment.coinpayments.logo;
            },
            getIsDirect: function () {
                return window.checkoutConfig.payment.coinpayments.direct_mode;
            },
            getCoinpaymentsUrl: function () {
                return window.checkoutConfig.payment.coinpayments.url;
            },

            getBaseGrandTotal: function () {
                return window.checkoutConfig.totalsData.base_grand_total;
            },
            getGrandTotal: function () {
                return window.checkoutConfig.totalsData.grand_total;
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
                var shopCurrency = window.checkoutConfig.totalsData.base_currency_code;
                var elemToChange = $('#converted_amount_coinpayments');
                var total = this.getBaseGrandTotal();
                var currentCurrencyCode = $(event.target).find('option:selected').val();

                var currentCurrency = this.getCurrencies().find(function (e, i, array) {
                    return e.value == currentCurrencyCode ? true : false
                });
                if (!currentCurrency) return;

                var shopCurrencyValue = this.getCurrencies().find(function (e, i, array) {
                    return e.value == shopCurrency ? true : false
                });
                if (currentCurrencyCode === 'BTC') {
                    total = (shopCurrencyValue.body.rate_btc * total);
                } else {
                    total = (shopCurrencyValue.body.rate_btc * total) / currentCurrency.body.rate_btc;
                }
                total = total.toFixed(5);
                elemToChange.val(total + ' ' + currentCurrencyCode);

                window.checkoutConfig.payment.coinpayments.currentData = {
                    total: total,
                    currency: currentCurrencyCode
                };
            },
            /**
             *
             * @returns {boolean}
             */
            getAllowPlaceOrder: function () {
                return this.getCode() === this.isChecked();
            },
            /**
             *
             * @param currency
             * @param value
             */
            saveCurrencyToQuote: function (currency, value) {
                var quoteId = quote.getQuoteId();
                var url = '/rest/V1/coinpayments/' + quoteId + '/currency';
                var data = {
                    'currency': currency,
                    'value': value
                };
                $.ajax({
                    type: "POST",
                    url: url,
                    contentType: "application/json",
                    data: JSON.stringify(data),
                    success: function (result) {
                        //TODO success logic
                    },
                    error: function (err) {
                        //TODO error logic
                    }
                });
            },
            /**
             *
             * @param currency
             * @param value
             * @param redirect
             */
            createTransaction: function (currency, value, redirect) {
                var quoteId = quote.getQuoteId();
                var url = '/rest/V1/coinpayments/' + quoteId + '/transaction';
                var data = {
                    'currency': currency,
                    'value': value
                };
                $.ajax({
                    type: "POST",
                    url: url,
                    contentType: "application/json",
                    data: JSON.stringify(data),
                    success: function (result) {
                        console.log(result);
                        if (redirect) {
                            window.location.replace(redirect);
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
        });
    }
);
