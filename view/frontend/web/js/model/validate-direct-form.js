define(
    ['jquery'],
    function ($) {
        'use strict';
        return {
            getCode: function () {
                return 'coin_payments';
            },
            /**
             * @returns {boolean}
             */
            validate: function() {
                if (this.getIsDirect()) {
                    return !this.validateDirectMode()
                }

                return true;
            },
            /**
             * @returns {boolean}
             */
            validateDirectMode: function () {
                var isError = false;
                var self = this;
                var code = self.getCode();
                if (!$("#" + code + "_crypto_currency").find('option:selected').val()) {
                    // self.reportCurrencyMessage();
                    isError = true;
                }
                return isError;
            },
            getIsDirect: function () {
                return window.checkoutConfig.payment.coinpayments.direct_mode;
            },
            reportCurrencyMessage: function (message)
            {
                $("#" + this.getCode() + "_crypto_currency_error").text(message);
            }
        }
    }
);