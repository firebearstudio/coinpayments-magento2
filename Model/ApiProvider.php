<?php

namespace Coinpayments\CoinPayments\Model;

use Magento\Checkout\Model\Session;
use \Magento\Checkout\Model\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Url;

class ApiProvider
{
    /* @var Session */
    protected $checkoutSession;
    /* @var Curl */
    protected $curl;

    /* @var ScopeConfigInterface */
    protected $scopeConfig;

    /* @var array */
    protected $defaultHeaders = [
        'Content-Type' => 'application/json'
    ];

    /* @var Cart */
    protected $cart;

    /**
     * @var Url
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var mixed
     */
    protected $baseConf;

    public function __construct(
        Session $checkoutSession,
        Cart $cart,
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        Url $urlBuilder
    )
    {
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->baseConf = $this->getBaseConfig();
    }

    public function getRequestHeaders($requestParams, $requestData = [])
    {

        $date = new \Datetime();

        $headerData = [
            'method' => $requestParams['method'],
            'url' => $this->getApiUrl($requestParams['action']),
            'clientId' => $requestParams['clientId'],
            'timestamp' => $date->format('c'),
        ];

        if (!empty($requestData)) {
            $headerData['params'] = json_encode($requestData);
        }

        return [
            'X-CoinPayments-Client' => $requestParams['clientId'],
            'X-CoinPayments-Timestamp' => $date->format('c'),
            'X-CoinPayments-Signature' => $this->generateHmac($headerData, $requestParams['clientSecret']),
        ];
    }

    /**
     * @param $action
     * @param $headers
     * @param $requestData
     * @return mixed
     */
    public function sendPostRequest($action, $headers, $requestData)
    {

        $headers = array_merge($headers, $this->defaultHeaders);
        foreach ($headers as $name => $value) {
            $this->curl->addHeader($name, $value);
        }

        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);

        $requestUrl = $this->getApiUrl($action);
        $this->curl->post($requestUrl, $requestData);
        return json_decode($this->curl->getBody(), true);
    }

    /**
     * @param $action
     * @param $headers
     * @param array $requestData
     * @return mixed
     */
    public function sendGetRequest($action, $headers, $requestData = [])
    {

        $headers = array_merge($headers, $this->defaultHeaders);
        foreach ($headers as $name => $value) {
            $this->curl->addHeader($name, $value);
        }

        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);

        $requestUrl = $this->getApiUrl($action);
        if (!empty($requestData)) {
            $requestUrl .= '?' . http_build_query($requestData);
        }

        $this->curl->get($requestUrl);
        return json_decode($this->curl->getBody(), true);
    }

    /**
     * @param $baseConf
     * @param $action
     * @return string
     */
    public function getApiUrl($action)
    {
        return sprintf('%s/v%s/%s', $this->baseConf['api_url'], $this->baseConf['api_version'], $action);
    }

    /**
     * @param $requestData
     * @param null $secretKey
     * @return string
     */
    public function generateHmac($requestData, $secretKey = null)
    {
        return base64_encode(hash_hmac('sha256', chr(239) . implode('', $requestData), $secretKey, true));
    }

    /**
     * @param $base
     * @param $field
     * @return mixed
     */
    protected function getConfig($base, $field = false)
    {
        if ($field) {
            return $this->scopeConfig->getValue($base . DIRECTORY_SEPARATOR . $field);
        }
        return $this->scopeConfig->getValue($base);
    }

    /**
     * @param null $field
     * @return mixed
     */
    protected function getBaseConfig($field = null)
    {
        return $this->getConfig('coinpayment/conf', $field);
    }
}
