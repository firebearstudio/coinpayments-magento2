<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 21.11.18
 * Time: 22:23
 */

namespace Coinpayments\CoinPayments\Model\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;

class Rate extends Base
{
    /**
     * Rate constructor.
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Curl $curl, ScopeConfigInterface $scopeConfig)
    {
        parent::__construct($curl, $scopeConfig);
    }

    /**
     * @param $accepted
     * @param bool $assoc
     * @return mixed
     */
    public function fetchRates($accepted, $assoc = false)
    {
        $data = ['accepted' => $accepted];
        $rates = $this->sendRequest($data, 'rates', [], 'post', $assoc);
        return $rates;
    }


    //TODO normal logic for exchange rate
    /**
     * @param $currency
     * @param $amount
     * @return float|int
     */
    public function getConverted($currency, $amount)
    {
        $ratesData = $this->fetchRates(false, false);

        if (strtolower($ratesData->error) != 'ok') {
            return 0;
        }
        $rates = $ratesData->result;

        $rate = $currency != 'BTC' ?
            ($amount * $rates->USD->rate_btc) / $rates->$currency->rate_btc :
            $amount * $rates->USD->rate_btc;
        return round($rate, 8);
    }
}