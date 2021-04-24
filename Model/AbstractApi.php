<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Model\Methods\Coinpayments;
use Coinpayments\CoinPayments\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;

/**
 * Class AbstractApi
 * @package Coinpayments\CoinPayments\Model
 */
abstract class AbstractApi
{

    /* @var Curl */
    protected $curl;
    /* @var ScopeConfigInterface */
    protected $scopeConfig;
    /* @var array */
    protected $defaultHeaders = [
        'Content-Type' => 'application/json;'
    ];
    /**
     * @var Order
     */
    protected $orderModel;
    /**
     * @var Coinpayments
     */
    protected $coinPaymentsMethod;
    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Repository
     */
    protected $transactionRepository;

    /**
     * AbstractApi constructor.
     * @param Curl $curl
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param Order $orderModel
     * @param Coinpayments $coinPaymentsMethod
     * @param BuilderInterface $transactionBuilder
     * @param Repository $transactionRepository
     */
    public function __construct(
        Curl $curl,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        Order $orderModel,
        Coinpayments $coinPaymentsMethod,
        BuilderInterface $transactionBuilder,
        Repository $transactionRepository
    )
    {
        $this->curl = $curl;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->orderModel = $orderModel;
        $this->coinPaymentsMethod = $coinPaymentsMethod;
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionRepository = $transactionRepository;

    }

    /**
     * @param $requestParams
     * @param array $requestData
     * @return array
     * @throws \Exception
     */
    public function getRequestHeaders($requestParams, $requestData = [])
    {

        $date = new \Datetime();

        $headerData = [
            'method' => $requestParams['method'],
            'url' => $this->helper->getApiUrl($requestParams['action']),
            'clientId' => $requestParams['clientId'],
            'timestamp' => $date->format('c'),
        ];

        if (!empty($requestData)) {
            $headerData['params'] = json_encode($requestData);
        }

        return [
            'X-CoinPayments-Client' => $requestParams['clientId'],
            'X-CoinPayments-Timestamp' => $date->format('c'),
            'X-CoinPayments-Signature' => $this->generateHmac($headerData, $requestParams['clientSecret'], true),
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

        $requestUrl = $this->helper->getApiUrl($action);
        $this->curl->post($requestUrl, json_encode($requestData));
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

        $requestUrl = $this->helper->getApiUrl($action);
        if (!empty($requestData)) {
            $requestUrl .= '?' . http_build_query($requestData);
        }

        $this->curl->get($requestUrl);
        return json_decode($this->curl->getBody(), true);
    }

    /**
     * @param $requestData
     * @param null $secretKey
     * @param bool $useAdditional
     * @return string
     */
    public function generateHmac($requestData, $secretKey = null, $useAdditional = false)
    {
        if ($useAdditional) {
            $requestData = array_merge([chr(239), chr(187), chr(191)], $requestData);
        }

        return base64_encode(
            hash_hmac(
                'sha256',
                implode('', $requestData),
                $secretKey,
                true
            )
        );
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getCurrencies($params = [])
    {
        return $this->sendGetRequest('currencies', [], $params);
    }
}
