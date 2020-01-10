<?php

namespace Coinpayments\CoinPayments\Api;

interface WebHookInterface
{

    public function getWebHookCallbackUrl();

    public function createWebHook($clientId, $clientSecret);

    public function getList($clientId, $clientSecret);
}