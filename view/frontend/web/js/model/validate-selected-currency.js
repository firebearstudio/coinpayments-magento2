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
            validate: function () {
                var valid = true;

                var selector = $("#" + this.getCode() + "_crypto_currency");

                if (!selector.find('option:selected').val()) {
                    selector.addClass('mage-error');
                    valid = false;
                } else {
                    selector.removeClass('mage-error');
                }

                return valid;
            }
        }
    }
);