<?php

namespace Coinpayments\CoinPayments\Model;

interface IpnInterface
{
    /**
     * @param $data
     * @param $hmac
     * @return mixed
     */
    public function processIpnRequest($data, $hmac);
}
