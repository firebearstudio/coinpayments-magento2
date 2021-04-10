<?php

namespace Coinpayments\CoinPayments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Repository;


class CoinPaymentsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Curl
     */
    protected $_curl;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var Repository
     */
    protected $_assetRepo;
    /**
     * @var string
     */
    protected $_methodCode = 'coin_payments';

    /**
     * CoinPaymentsConfigProvider constructor.
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepo
     */
    public function __construct(
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo
    )
    {
        $this->_curl = $curl;
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $secretKey = $this->_scopeConfig->getValue('payment/coin_payments/secret_key', $scope);
        $publicKey = $this->_scopeConfig->getValue('payment/coin_payments/public_key', $scope);
        $isDirect = $this->_scopeConfig->getValue('payment/coin_payments/is_direct', $scope);
        $coinpaymentsDomain = $this->_scopeConfig->getValue('coinpayment/conf/url_payment', $scope);
        $coinpaymentsApi = $this->_scopeConfig->getValue('coinpayment/conf/api_payment', $scope);

        $data = [
            'version' => 1,
            'cmd' => 'rates',
            'key' => $publicKey,
            'accepted' => 2,
        ];

        $this->_curl->addHeader('HMAC', hash_hmac('sha512', http_build_query($data), $secretKey));
        $this->_curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->_curl->post($coinpaymentsApi, $data);
        $response = json_decode($this->_curl->getBody());

        $currencies = ['error' => $response->error];
        $acceptedCurrencies = ['error' => $response->error];
        if ($response->error == 'ok') {
            $currencies = [];
            $acceptedCurrencies = [];
            foreach ($response->result as $key => $item) {
                $elm = [
                    'value' => $key,
                    'body' => $item,
                    'name' => $item->name
                ];
                $currencies[] = $elm;
                if (isset($item->accepted) && $item->accepted == '1') {
                    $acceptedCurrencies[] = $elm;
                }
            }
        }

        return [
            'payment' => [
                'coinpayments' => [
                    'available_currencies' => $currencies,
                    'accepted_currencies' => $acceptedCurrencies,
                    'logo' => $this->_assetRepo->getUrl('Coinpayments_CoinPayments::images/logo.png'),
                    'direct_mode' => (int)$isDirect,
                    'url' => $coinpaymentsDomain,
                    'api_url' => $coinpaymentsApi
                ]
            ]
        ];
    }
}