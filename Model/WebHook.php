<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Api\WebHookInterface;
use Magento\Sales\Model\Order;

class WebHook extends AbstractApi implements WebHookInterface
{

    /**
     * @param $clientId
     * @param $clientSecret
     * @param $webHookCallbackUrl
     * @return mixed
     * @throws \Exception
     */
    public function createWebHook($clientId, $clientSecret, $webHookCallbackUrl)
    {

        $action = sprintf('merchant/clients/%s/webhooks', $clientId);

        $requestParams = [
            'method' => 'POST',
            'action' => $action,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ];

        $requestData = [
            "notificationsUrl" => $webHookCallbackUrl,
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

    /**
     * @param $clientId
     * @param $clientSecret
     * @return mixed
     * @throws \Exception
     */
    public function getList($clientId, $clientSecret)
    {

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

    /**
     * @param $requestData
     * @throws \Exception
     */
    public function cancelOrder($requestData)
    {
        $order = $this->orderModel->loadByIncrementId($requestData['invoice']['invoiceId']);
        if ($order->getId()) {
            $order
                ->setStatus(Order::STATE_CANCELED)
                ->setState(Order::STATE_CANCELED)
                ->save();
        }
    }

    /**
     * @param $order
     * @param $requestData
     * @return bool
     */
    public function completeOrder($requestData)
    {
        $order = $this->orderModel->loadByIncrementId($requestData['invoice']['invoiceId']);
        if ($order->getId()) {
            $order->setTotalPaid($requestData['invoice']['amount']['displayValue']);

            $order
                ->setState($this->scopeConfig->getValue('payment/coin_payments' . DIRECTORY_SEPARATOR . 'status_order_paid'))
                ->setStatus($this->scopeConfig->getValue('payment/coin_payments' . DIRECTORY_SEPARATOR . 'status_order_paid'));

            $str = 'CoinPayments.net Payment Status: <strong>' . $requestData['status'] . '</strong> ' . $requestData['status'] . '<br />';

            $str .= 'Transaction ID: ' . $requestData['invoice']['invoiceId'] . '<br />';
            $str .= 'Received Amount: ' . sprintf('%s %s', $requestData['invoice']['amount']['displayValue'], $requestData['invoice']['currency']['symbol']);

            $order->addStatusToHistory($order->getStatus(), $str);


            try {
                $payment = $order->getPayment();
                $payment->setMethod($this->coinPaymentsMethod->getCode());
                $payment->setLastTransId($requestData['invoice']['invoiceId']);
                $payment->setTransactionId($requestData['invoice']['invoiceId']);
                $payment->setAdditionalInformation([Order\Payment\Transaction::RAW_DETAILS => (array)$requestData]);

                $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());

                /* @var Order\Payment\Transaction\BuilderInterface */
                $transaction = $this->transactionBuilder
                    ->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($requestData['invoice']['invoiceId'])
                    ->setAdditionalInformation([Order\Payment\Transaction::RAW_DETAILS => (array)$requestData])
                    ->setFailSafe(true)
                    ->build(Order\Payment\Transaction::TYPE_CAPTURE);

                // Add transaction to payment
                $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
                $payment->setParentTransactionId(null);

                // Save payment, transaction and order
                $payment->save();
                $order->save();
                $transaction->save();
                return $transaction->getTransactionId();

            } catch (\Exception $e) {

            }

        }

        return true;
    }


}