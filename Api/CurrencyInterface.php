<?php
namespace Coinpayments\CoinPayments\Api;

interface CurrencyInterface
{


    const CRYPTO_TYPE = 'crypto';
    const FIAT_TYPE = 'fiat';
    const PAYMENT_CAPABILITY = 'payments';

    /**
     * @return array
     */
    public function getCurrencies();

    /**
     * @param int $currencyId
     * @param int $coinCurrencyId
     * @return mixed
     */
    public function getCurrencyRate($currencyId, $coinCurrencyId);

    /**
     * @param int $coinCurrencyId
     * @param int $coinCurrencyPrecision
     * @return mixed
     */
    public function setCurrency($coinCurrencyId, $coinCurrencyPrecision);
}