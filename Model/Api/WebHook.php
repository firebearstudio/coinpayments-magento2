<?php

namespace Coinpayments\CoinPayments\Model\Api;

use Coinpayments\CoinPayments\Api\WebHookInterface;
use Coinpayments\CoinPayments\Model\ApiProvider;

class WebHook extends ApiProvider implements WebHookInterface
{

    public function getWebHookCallbackUrl()
    {
        return $this->urlBuilder->getUrl('rest/V1/coinpayments/invoice/notification', ['_direct' => null]);
    }

    public function createWebHook($clientId, $clientSecret)
    {

        return [ // TODO delete test data
            "id" => "testId",
            "notificationsUrl" => $this->getWebHookCallbackUrl(),
            "notifications" => [
                "invoiceCreated",
            ]
        ];

        $action = sprintf('merchant/clients/%s/webhooks', $clientId);

        $requestParams = [
            'method' => 'POST',
            'action' => $action,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ];

        $requestData = [
            "notificationsUrl" => $this->getWebHookCallbackUrl(),
            "notifications" => [
                "invoiceCreated",
                "invoicePaymentsReceived",
                "invoicePaymentsConfirmed",
                "invoiceCompleted",
                "invoiceExpired",
            ],
        ];

        $headers = $this->getRequestHeaders($requestParams, $requestData);
        return $this->sendPostRequest($action, $headers, $requestData);
    }

    public function getList($clientId, $clientSecret)
    {

        return [ // TODO delete test data
            "items" => [
                [
                    "id" => "testId",
                    "notificationsUrl" => "https://magento2.test/rest/V1/coinpayments/invoice/notification/",
                    "notificationsUrl" => "https://magento2.test/rest/V1/coinpayments/",
                    "notifications" => [
                        "invoiceCreated",
                    ]
                ]
            ]
        ];

        $action = sprintf('merchant/clients/%s/webhooks', $clientId);
        $requestParams = [
            'method' => 'GET',
            'action' => $action,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ];

        $headers = $this->getRequestHeaders($requestParams);

        return $this->sendGetRequest($action, $headers);
    }


}