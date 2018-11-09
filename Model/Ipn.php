<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Helper\Data;
use Coinpayments\CoinPayments\Logger\Logger;
use Coinpayments\CoinPayments\Model\Methods\Coinpayments;
use Magento\Sales\Model\Order;

class Ipn extends AbstractIpn implements IpnInterface
{
    public function __construct(
        Order $orderModel,
        Data $helper,
        Logger $logger,
        Coinpayments $coinpaymentsModel,
        Order\Payment\Transaction\BuilderInterface $transactionBuilder
    )
    {
        parent::__construct($orderModel, $helper, $logger, $coinpaymentsModel, $transactionBuilder);
    }

    public function processIpnRequest($data, $hmac)
    {
        $error = [];
        $this->setHmac($hmac);
        $this->setRequestData($data);
        $order = $this->getOrder();

        $this
            ->logAndDie('REQUEST: ' . print_r($data, true))
            ->logAndDie('HMAC: ' . $hmac);

        if (!$order) {
            $error['order'] = __('Order is not longer exist');
            $this->logAndDie($error['order']);
            return $error;
        }

        if (!$this->filterIpnType()) {
            $error['ipn_type'] = __('Invalid IPN type');
            $this->logAndDie($error['ipn_type']);
            return $error;
        }

        if (!$this->checkHmac()) {
            $error['hmac'] = __('Invalid HMAC signature');
            $this->logAndDie($error['hmac']);
            return $error;
        }

        $this
            ->updateOrderPayment()
            ->updateOrderStatus()
            ->addToOrderHistory()
            ->addTransactionToOrder();

        try {
            $order->save();
        } catch (\Exception $e) {
            $error['order_save'] = __('Error when save Order');
            $this->logAndDie($error['order_save']);
            return $error;
        }
        $this->logAndDie(__('SUCCESS UPDATE ORDER'));
        return [];
    }
}