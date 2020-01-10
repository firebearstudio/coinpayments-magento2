<?php

namespace Coinpayments\CoinPayments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Repository;
use Coinpayments\CoinPayments\Model\Api\Currency as CurrencyApi;


class CoinPaymentsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var Repository
     */
    protected $_assetRepo;
    /**
     * @var CurrencyApi
     */
    protected $_apiCurrency;
    /**
     * @var string
     */
    protected $_methodCode = 'coin_payments';

    /**
     * CoinPaymentsConfigProvider constructor.
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepo
     * @param CurrencyApi $apiCurrency
     */
    public function __construct(Curl $curl, ScopeConfigInterface $scopeConfig, Repository $assetRepo, CurrencyApi $apiCurrency)
    {
        $this->curl = $curl;
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
        $this->_apiCurrency = $apiCurrency;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $currencies = $this->getCryptoCurrencies();

        return [
            'payment' => [
                'coinpayments' => [
                    'currencies' => $currencies,
                    'logo' => $this->_assetRepo->getUrl('Coinpayments_CoinPayments::images/logo.png'),
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getCryptoCurrencies()
    {
        $currencies = [];

        $apiCurrencies = $this->_apiCurrency->getAllCryptoCurrencies();

        if (!empty($apiCurrencies)) {
            foreach ($apiCurrencies as $key => $item) {
                $elm = [
                    'value' => $item['currencyId'],
                    'name' => $item['name'],
                    'body' => $item,
                ];
                $currencies[] = $elm;
            }
        } else {
            $currencies = [
                'error' => [
                    'name' => 'Cannot load Coinpayments.Net currencies',
                    'value' => 'error',
                    'attr' => [
                        'disabled' => true,
                    ],
                ],
            ];
        }

        return $currencies;
    }
}
