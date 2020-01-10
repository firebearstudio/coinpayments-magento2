<?php

namespace Coinpayments\CoinPayments\Model\Api;

use Coinpayments\CoinPayments\Api\InvoiceInterface;
use Coinpayments\CoinPayments\Model\ApiProvider;

class Invoice extends ApiProvider implements InvoiceInterface
{

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function createInvoice()
    {
        $currencyId = $this->checkoutSession->getCoinpaymentsCurrency();
        $amount = $this->checkoutSession->getCoinpaymentsAmount();
        $order = $this->checkoutSession->getLastRealOrder();

        $clientId = $this->scopeConfig->getValue('payment/coin_payments' . DIRECTORY_SEPARATOR . 'client_id');
        $clientSecret = $this->scopeConfig->getValue('payment/coin_payments' . DIRECTORY_SEPARATOR . 'client_secret');
        $merchantWebHooks = $this->scopeConfig->getValue('payment/coin_payments' . DIRECTORY_SEPARATOR . 'webhooks');

        if ($merchantWebHooks) {
            $invoiceData = $this->createMerchant($clientId, $clientSecret, $currencyId, $order->getIncrementId(), intval($amount));
        } else {
            $invoiceData = $this->createSimple($clientId, $currencyId, $order->getIncrementId(), intval($amount));
        }

        $response = [];

        if (!empty($invoiceData['id'])) {
            $this->checkoutSession->setCoinpaymentsInvoiceId($invoiceData['id']);
            $response = ['coinInvoiceId' => $invoiceData['id']];
        }

        return $response;
    }

    /**
     * @return boolean
     */
    public function updateInvoice()
    {
        $response = false;

        return $response;
    }

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

        $action = sprintf('merchant/invoices', $clientId);

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