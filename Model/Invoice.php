<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Api\InvoiceInterface;
use Coinpayments\CoinPayments\Helper\Data;
use Magento\Sales\Model\Order;

/**
 * Class Invoice
 * @package Coinpayments\CoinPayments\Model
 */
class Invoice extends AbstractApi implements InvoiceInterface
{

    /**
     * @param $clientId
     * @param $clientSecret
     * @param $currencyId
     * @param $invoiceId
     * @param $amount
     * @param $displayValue
     * @return mixed
     * @throws \Exception
     */
    public function createMerchant($clientId, $clientSecret, $currencyId, $invoiceId, $amount, $displayValue)
    {

        $action = Data::API_MERCHANT_INVOICE_ACTION;

        $requestParams = [
            'method' => 'POST',
            'action' => $action,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ];

        $requestData = [
            "invoiceId" => $invoiceId,
            "amount" => [
                "currencyId" => $currencyId,
                "displayValue" => $displayValue,
                "value" => $amount
            ],
        ];

        $requestData = $this->appendInvoiceMetadata($requestData);
        $headers = $this->getRequestHeaders($requestParams, $requestData);
        return $this->sendPostRequest($action, $headers, $requestData);
    }

    /**
     * @param $clientId
     * @param int $currencyId
     * @param string $invoiceId
     * @param int $amount
     * @param string $displayValue
     * @return mixed
     */
    public function createSimple($clientId, $currencyId = 5057, $invoiceId = 'Validate invoice', $amount = 1, $displayValue = '0.01')
    {

        $action = Data::API_SIMPLE_INVOICE_ACTION;

        $requestData = [
            'clientId' => $clientId,
            'invoiceId' => $invoiceId,
            'amount' => [
                'currencyId' => $currencyId,
                "displayValue" => $displayValue,
                'value' => $amount
            ],
        ];

        $requestData = $this->appendInvoiceMetadata($requestData);
        return $this->sendPostRequest($action, [], $requestData);
    }

    /**
     * @param $order
     * @param $coinInvoiceId
     * @return int
     */
    public function createOrderTransaction($order, $coinInvoiceId)
    {
        $payment = $order->getPayment();
        /* @var Order\Payment\Transaction\BuilderInterface */
        $transaction = $this->transactionBuilder
            ->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($coinInvoiceId)
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
        $transaction->save();
        return $transaction->getTransactionId();
    }

    protected function appendInvoiceMetadata($requestData)
    {

        $requestData['metadata'] = [
            "integration" => sprintf("Magento_v%s", $this->helper->getBaseConfigParam(Data::PACKAGE_VERSION)),
            "hostname" => $this->helper->getHostUrl(),
        ];

        return $requestData;
    }
}