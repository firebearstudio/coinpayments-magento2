<?php

namespace Coinpayments\CoinPayments\Api;

interface WebHookInterface
{

    /**
     * @param $clientId
     * @param $clientSecret
     * @param $webHookCallbackUrl
     * @return mixed
     */
    public function createWebHook($clientId, $clientSecret, $webHookCallbackUrl);

    /**
     * @param $clientId
     * @param $clientSecret
     * @return mixed
     */
    public function getList($clientId, $clientSecret);
}