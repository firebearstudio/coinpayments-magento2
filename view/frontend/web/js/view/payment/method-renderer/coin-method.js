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
        window.url = url;

        var a = Component.extend({
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
                this.createInvoice();
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
            createInvoice: function () {
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    contentType: "application/json",
                    url: url.build(coin_invoice_create_url),
                    success: function (result) {
                        if (result.coinInvoiceId) {
                            window.location.href = result.redirectUrl;
                        } else {
                            window.location.href = result.cancelUrl;
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
        window.a = a;
        return a;
    }
)
;