<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Helper\Data;
use Coinpayments\CoinPayments\Logger\Logger;
use Magento\Sales\Model\Order;

class Ipn extends AbstractIpn implements IpnInterface
{
    public function __construct(Order $orderModel, Data $helper, Logger $logger)
    {
        parent::__construct($orderModel, $helper, $logger);
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

        if (!$this->filterIpnType()) {
            $error['ipn_type'] = __('Invalid IPN type');
            $this->logAndDie(__('Invalid IPN type'));
            return $error;
        }

        if (!$this->checkHmac()) {
            $error['hmac'] = __('Invalid HMAC signature');
            $this->logAndDie(__('Invalid HMAC signature'));
            return $error;
        }

        $this
            ->updateOrderPayment()
            ->updateOrderStatus()
            ->addToOrderHistory();

        try {
            $order->save();
        } catch (\Exception $e) {
            $this->logAndDie(__('Error when save Order'));
            $error['order_save'] = __('Error when save Order');
            return $error;
        }
        $this->logAndDie(__('SUCCESS UPDATE ORDER'));
        return [];
    }
}