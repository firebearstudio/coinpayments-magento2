require([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {

    var validationError = "Please enter a valid Coinpayments.NET credentials.";

    $.validator.addMethod(
        "coin-webhooks-validate",
        function (client_secret, elem) {

            var is_success = false;
            var validator = this;
            var client_id_field = $('[data-ui-id="text-groups-coin-payments-fields-client-id-value"]');
            var client_secret_field = $('[data-ui-id="text-groups-coin-payments-fields-client-secret-value"]');
            var web_hooks = $('[data-ui-id="select-groups-coin-payments-fields-webhooks-value"]');

            if (client_id_field.length && web_hooks.length && client_secret_field.length) {

                var params = {
                    client_id: client_id_field.val(),
                    client_secret: client_secret_field.val(),
                };

                $.ajax({
                    showLoader: true,
                    url: coin_validate_webhooks,
                    data: params,
                    async: false,
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    is_success = data.success;
                    if (!is_success) {
                        var errors = {};
                        errors[client_id_field.attr('name').replace(/[.*+?^${}()|[\]\\]/g, '\\$&')] = validationError;
                        validator.showErrors(errors);
                    }
                });
            }

            return is_success;
        },
        $.mage.__(validationError)
    );
});