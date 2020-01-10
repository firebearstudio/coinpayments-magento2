<?php

namespace Coinpayments\CoinPayments\Model\Api;

use Coinpayments\CoinPayments\Model\ApiProvider;
use Coinpayments\CoinPayments\Api\CurrencyInterface;

class Currency extends ApiProvider implements CurrencyInterface
{

    /**
     * @param int $coinCurrencyId
     * @param $coinCurrencyPrecision
     * @return array|\Magento\Framework\Controller\Result\Json
     */
    public function setCurrency($coinCurrencyId, $coinCurrencyPrecision)
    {

        $quote = $this->cart->getQuote();
        $currencyCode = $quote->getBaseCurrencyCode();
        $amount = $quote->getBaseGrandTotal();
        $response = [];

        if ($coinShopCurrency = $this->getFiatCurrency($currencyCode)) {
            $currencyId = $coinShopCurrency['currencyId'];
            $convertedAmount = $this->convertCurrencies($currencyId, $coinCurrencyId, $amount, $coinCurrencyPrecision);
        }

        if (!empty($convertedAmount)) {

            $this->checkoutSession->setCoinpaymentsCurrency($coinCurrencyId);
            $this->checkoutSession->setCoinpaymentsAmount($convertedAmount['value']);

            $response = [
                'converted_amount' => $convertedAmount['displayValue'],
            ];
        }

//        $result = $this->jsonResultFactory->create();
//        $result->setData($response);
        return json_encode($response);
    }


    /**
     * @return array|mixed
     */
    public function getAllCryptoCurrencies()
    {

        $currencies = [];

        $listData = $this->getCryptoCurrencies();

        if (!empty($listData['items'])) {
            $currencies = $listData['items'];
            while (!empty($listData['next_page_params']) && $listData = $this->getCryptoCurrencies($listData['next_page_params'])) {
                if (!empty($listData['items'])) {
                    $currencies = array_merge($currencies, $listData['items']);
                }
            }
        }
        return $currencies;
    }


    /**
     * @param array $params
     * @return array|mixed
     */
    public function getCryptoCurrencies(array $params = [])
    {

        $params = array_merge($params, [
            'types' => self::CRYPTO_TYPE,
            'capabilities' => self::PAYMENT_CAPABILITY,
        ]);

        $listData = $this->getCurrencies($params);
        if (!empty($listData['paging']['next'])) {
            $nextPage = parse_url($listData['paging']['next']);
            $nextPage['query_params'] = Psr7\parse_query($nextPage['query']);
            $listData['next_page_params'] = $nextPage['query_params'];
        }

        return $listData;
    }

    /**
     * @param array $params
     * @return array|mixed
     */
    public function getFiatCurrency(string $name)
    {

        $params = [
            'types' => self::FIAT_TYPE,
            'q' => $name,
        ];
        $items = [];

        $listData = $this->getCurrencies($params);
        if (!empty($listData['items'])) {
            $items = $listData['items'];
        }

        return array_shift($items);
    }


    /**
     * @param array $params
     * @return mixed
     */
    public function getCurrencies($params = [])
    {
        return $this->sendGetRequest('currencies', [], $params);
    }

    /**
     * @param int $currencyId
     * @param $coinCurrencyId
     * @param $amount
     * @return bool
     */
    public function convertCurrencies($currencyId, $coinCurrencyId, $amount, $precision)
    {
        $rate = $this->getCurrencyRate($currencyId, $coinCurrencyId);

        $data['displayValue'] = $amount * $rate;
        $data['value'] = number_format($amount * $rate, $precision, '', '');

        return $data;
    }

    /**
     * @param $currencyId
     * @param $coinCurrencyId
     * @return bool|mixed
     */
    public function getCurrencyRate($currencyId, $coinCurrencyId)
    {

        $rate = false;

        $params = [
            'from' => $currencyId,
            'to' => $coinCurrencyId,
        ];

        $listData = $this->sendGetRequest('rates', [], $params);

        if (!empty($listData['items'])) {
            $rateData = array_shift($listData['items']);
            $rate = $rateData['rate'];
        }

        return $rate;
    }

}