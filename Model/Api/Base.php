<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 21.11.18
 * Time: 22:32
 */

namespace Coinpayments\CoinPayments\Model\Api;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;

class Base
{
    /* @var Curl */
    protected $_curl;
    /* @var ScopeConfigInterface */
    protected $_scopeConfig;
    /* @var array */
    private $_defaultHeaders = [
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    /**
     * Base constructor.
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct
    (
        Curl $curl,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_curl = $curl;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param $base
     * @param $field
     * @return mixed
     */
    protected function getConfig($base, $field)
    {
        if ($field) {
            return $this->_scopeConfig->getValue($base . DIRECTORY_SEPARATOR . $field);
        }
        return $this->_scopeConfig->getValue($base);
    }

    /**
     * @param null $field
     * @return mixed
     */
    public function getPaymentConfig($field = null)
    {
        return $this->getConfig('payment/coin_payments', $field);
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
     * @param $data
     * @param $cmd
     * @param array $headers
     * @param string $method
     * @param bool $assoc
     * @return mixed
     */
    public function sendRequest($data, $cmd, $headers = [], $method = 'post', $assoc = false)
    {
        $paymentConf = $this->getPaymentConfig();
        $baseConf = $this->getBaseConfig();
        
        $paymentConf['public_key'] = isset($paymentConf['public_key']) ? $paymentConf['public_key'] : '';
        $paymentConf['api_version'] = isset($paymentConf['api_version']) ? $paymentConf['api_version'] : '1';
        
        $additionalData = [
            'version' => $baseConf['api_version'],
            'cmd' => $cmd,
            'key' => $paymentConf['public_key']
        ];

        $data = array_merge($data, $additionalData);
        $headers = array_merge($headers, $this->_defaultHeaders);

        $this->_curl->addHeader('HMAC', $this->generateHmac($data));
        foreach ($headers as $name => $value) {
            $this->_curl->addHeader($name, $value);
        }
        $this->_curl->$method($baseConf['api_payment'], $data);

        return json_decode($this->_curl->getBody(), $assoc);
    }

    /**
     * @param $data
     * @param null $secretKey
     * @return string
     */
    public function generateHmac($data, $secretKey = null)
    {
        if (!$secretKey) {
            $secretKey = $this->getPaymentConfig('secret_key');
        }
        return hash_hmac('sha512', http_build_query($data), $secretKey);
    }
}
