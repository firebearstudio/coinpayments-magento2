<?php

namespace Coinpayments\CoinPayments\Api;

interface InvoiceInterface
{

    /**
     * @param $clientId
     * @param $invoiceParams
     * @return mixed
     */
    public function createSimple($clientId, $invoiceParams);

    /**
     * @param $clientId
     * @param $clientSecret
     * @param $invoiceParams
     * @return mixed
     */
    public function createMerchant($clientId, $clientSecret, $invoiceParams);

}
