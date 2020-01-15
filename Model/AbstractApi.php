<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Model\Methods\Coinpayments;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order;

abstract class AbstractApi
{

    const FIAT_TYPE = 'fiat';

    const INVOICE_CACHE_PREFIX = 'CoinpaymentsInvoice';

    /* @var Curl */
    protected $curl;

    /* @var ScopeConfigInterface */
    protected $scopeConfig;

    /* @var array */
    protected $defaultHeaders = [
        'Content-Type' => 'application/json;'
    ];

    /**
     * @var mixed
     */
    protected $baseConf;
    /**
     * @var Order
     */
    protected $orderModel;
    /**
     * @var Coinpayments
     */
    protected $coinPaymentsMethod;
    /**
     * @var Order\Payment\Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    public function __construct(
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        Order $orderModel,
        Coinpayments $coinPaymentsMethod,
        Order\Payment\Transaction\BuilderInterface $transactionBuilder
    )
    {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->orderModel = $orderModel;
        $this->coinPaymentsMethod = $coinPaymentsMethod;
        $this->transactionBuilder = $transactionBuilder;
        $this->baseConf = $this->getBaseConfig();
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

        $requestUrl = $this->getApiUrl($action);
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
        return sprintf('%s/api/v%s/%s', $this->baseConf['api_host'], $this->baseConf['api_version'], $action);
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

    /**
     * @param null $field
     * @return mixed
     */
    public function getBaseConfig($field = null)
    {
        return $this->getConfig('coinpayment/conf', $field);
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
}
