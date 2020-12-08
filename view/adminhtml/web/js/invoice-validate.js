require([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {

    $.validator.addMethod(
        "coin-invoice-validate",
        function (client_id) {

            var web_hooks = $('[data-ui-id="select-groups-coin-payments-fields-webhooks-value"]');
            var is_success = false;

            if (web_hooks.length && web_hooks.val() === '1') {
                is_success = true;
            } else {
                var params = {
                    client_id: client_id,
                };
                $.ajax({
                    showLoader: true,
                    url: coin_validate_invoice,
                    data: params,
                    async: false,
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    is_success = data.success;
                });
            }

            return is_success;
        },
        $.mage.__("Please enter a valid Coinpayments.NET credentials.")
    );

});