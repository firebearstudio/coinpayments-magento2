<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Api\InvoiceInterface;
use Magento\Sales\Model\Order;

class Invoice extends AbstractApi implements InvoiceInterface
{

    /**
     * @param $clientId
     * @param $clientSecret
     * @param $currencyId
     * @param $invoiceId
     * @param $amount
     * @return mixed
     * @throws \Exception
     */
    public function createMerchant($clientId, $clientSecret, $currencyId, $invoiceId, $amount)
    {
        $requestData = [
            "invoiceId" => $invoiceId,
            "amount" => [
                "currencyId" => $currencyId,
                "value" => $amount
            ],
        ];

        $action = 'merchant/invoices';

        $requestParams = [
            'method' => 'POST',
            'action' => $action,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ];

        $headers = $this->getRequestHeaders($requestParams, $requestData);

        return $this->sendPostRequest($action, $headers, $requestData);
    }

    /**
     * @param $clientId
     * @param int $currencyId
     * @param string $invoiceId
     * @param int $amount
     * @return mixed
     */
    public function createSimple($clientId, $currencyId = 1, $invoiceId = 'Validate invoice', $amount = 1)
    {

        $action = 'invoices';

        $requestParams = [
            'clientId' => $clientId,
            'invoiceId' => $invoiceId,
            'amount' => [
                'currencyId' => $currencyId,
                'value' => $amount
            ]
        ];

        return $this->sendPostRequest($action, [], $requestParams);
    }


}