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
     * @param $invoiceParams
     * @return mixed
     * @throws \Exception
     */
    public function createMerchant($clientId, $clientSecret, $invoiceParams)
    {

        $action = Data::API_MERCHANT_INVOICE_ACTION;

        $requestParams = [
            'method' => 'POST',
            'action' => $action,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ];

        $requestData = [
            'invoiceId' => $invoiceParams['invoiceId'],
            'amount' => [
                'currencyId' => $invoiceParams['currencyId'],
                'displayValue' => $invoiceParams['displayValue'],
                'value' => $invoiceParams['amount']
            ],
            'notesToRecipient' => $invoiceParams['notesLink'],
        ];

        if (isset($invoiceParams['billingData'])) {
            $requestData['buyer'] = $this->appendBillingData($invoiceParams['billingData']);
        }

        $requestData = $this->appendInvoiceMetadata($requestData);
        $headers = $this->getRequestHeaders($requestParams, $requestData);
        return $this->sendPostRequest($action, $headers, $requestData);
    }

    /**
     * @param $clientId
     * @param $invoiceParams
     * @return mixed
     */
    public function createSimple($clientId, $invoiceParams)
    {

        $action = Data::API_SIMPLE_INVOICE_ACTION;

        $requestData = [
            'clientId' => $clientId,
            'invoiceId' => $invoiceParams['invoiceId'],
            'amount' => [
                'currencyId' => $invoiceParams['currencyId'],
                'displayValue' => $invoiceParams['displayValue'],
                'value' => $invoiceParams['amount'],
            ],
            'notesToRecipient' => $invoiceParams['notesLink'],
        ];

        if (isset($invoiceParams['billingData'])) {
            $requestData['buyer'] = $this->appendBillingData($invoiceParams['billingData']);
        }

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

    /**
     * @param $requestData
     * @return mixed
     */
    protected function appendInvoiceMetadata($requestData)
    {

        $requestData['metadata'] = [
            'integration' => sprintf('Magento_v%s', $this->helper->getBaseConfigParam(Data::PACKAGE_VERSION)),
            'hostname' => $this->helper->getHostUrl(),
        ];

        return $requestData;
    }

    /**
     * @param $billingData
     * @return array
     */
    function appendBillingData($billingData)
    {
        $params = array(
            'companyName' => $billingData->getCompany(),
            'name' => array(
                'firstName' => $billingData->getFirstname(),
                'lastName' => $billingData->getLastname(),
            ),
            'phoneNumber' => $billingData->getTelephone(),
        );

        if (preg_match('/^.*@.*$/', $billingData->getEmail())) {
            $params['emailAddress'] = $billingData->getEmail();
        }

        if (!empty($billingData->getStreetLine(1)) &&
            !empty($billingData->getCity()) &&
            preg_match('/^([A-Z]{2})$/', $billingData->getCountryId())
        ) {
            $params['address'] = array(
                'address1' => $billingData->getStreetLine(1),
                'address2' => $billingData->getStreetLine(2),
                'provinceOrState' => $billingData->getRegionCode(),
                'city' => $billingData->getCity(),
                'countryCode' => $billingData->getCountryId(),
                'postalCode' => $billingData->getPostcode()
            );
        }

        return $params;
    }
}
