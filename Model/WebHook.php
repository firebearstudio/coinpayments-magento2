<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Api\WebHookInterface;
use Coinpayments\CoinPayments\Helper\Data;
use Magento\Sales\Model\Order;

/**
 * Class WebHook
 * @package Coinpayments\CoinPayments\Model
 */
class WebHook extends AbstractApi implements WebHookInterface
{

    /**
     * @param $clientId
     * @param $clientSecret
     * @param $event
     * @return mixed
     * @throws \Exception
     */
    public function createWebHook($clientId, $clientSecret, $event)
    {

        $action = sprintf(Data::API_WEBHOOK_ACTION, $clientId);

        $requestParams = [
            'method' => 'POST',
            'action' => $action,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ];

        $requestData = [
            "notificationsUrl" => $this->helper->getNotificationUrl($clientId, $event),
            "notifications" => [
                sprintf("invoice%s", $event),
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

        $action = sprintf(Data::API_WEBHOOK_ACTION, $clientId);

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
     * @throws \Magento\Framework\Exception\InputException
     */
    public function receiveNotification($requestData)
    {

        $orderData = explode('|', $requestData['invoice']['invoiceId']);
        $host = array_shift($orderData);
        if ($host == md5($this->helper->getHostUrl())) {
            $orderId = array_shift($orderData);
            $paymentId = array_shift($orderData);
            $coinInvoiceId = $requestData['invoice']['id'];
            $transaction = $this->transactionRepository->getByTransactionId(
                $coinInvoiceId,
                $paymentId,
                $orderId
            );

            if (!empty($transaction)) {
                /** @var Order $order */
                $order = $transaction->getOrder();
                if ($requestData['invoice']['status'] == Data::PAID_EVENT) {
                    $this->completeOrder($requestData['invoice'], $order, $transaction);
                } elseif ($requestData['invoice']['status'] == Data::CANCELLED_EVENT) {
                    $this->cancelOrder($order);
                }
            }
        }

    }

    /**
     * @param $rawDetails
     * @param $order
     * @param $transaction
     * @return bool
     */
    public function completeOrder($rawDetails, $order, $transaction)
    {

        $rawDetails['amount'] = $rawDetails['amount']['displayValue'];
        $rawDetails['currency'] = $rawDetails['currency']['symbol'];


        $order->setTotalPaid($rawDetails['amount']);
        $order
            ->setState($this->helper->getConfig(Data::CLIENT_ORDER_STATUS_KEY))
            ->setStatus($this->helper->getConfig(Data::CLIENT_ORDER_STATUS_KEY));

        $str = 'CoinPayments.net Payment Status: <strong>' . $rawDetails['status'] . '</strong> ' . $rawDetails['status'] . '<br />';
        $str .= 'Transaction ID: ' . $rawDetails['id'] . '<br />';
        $str .= 'Received Amount: ' . sprintf('%s %s', $rawDetails['amount'], $rawDetails['currency']);
        $order->addStatusToHistory($order->getStatus(), $str);


        $transaction->setAdditionalInformation(Order\Payment\Transaction::RAW_DETAILS, $rawDetails);

        try {
            $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());

            $payment = $order->getPayment();
            $payment->setMethod($this->coinPaymentsMethod->getCode());
            $payment->setLastTransId($transaction->getId());
            $payment->setTransactionId($transaction->getTransactionId());
            $payment->setAdditionalInformation([Order\Payment\Transaction::RAW_DETAILS => $rawDetails]);
            $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
            $payment->setParentTransactionId(null);

            $invoice = $order->prepareInvoice()->register();
            $invoice->setOrder($order);
            $invoice->setOrder($order);
            $invoice->pay();

            $order->addRelatedObject($invoice);
            $payment->setCreatedInvoice($invoice);

            $payment->save();
            $order->save();
            $transaction->save();
            return $transaction->getTransactionId();
        } catch (\Exception $e) {
        }
        return true;
    }

    /**
     * @param Order $order
     * @throws \Exception
     */
    public function cancelOrder($order)
    {
        $order
            ->setStatus(Order::STATE_CANCELED)
            ->setState(Order::STATE_CANCELED)
            ->save();
    }

}
