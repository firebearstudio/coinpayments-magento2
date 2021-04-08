<?php

namespace Coinpayments\CoinPayments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;

/**
 * Class Data
 * @package Coinpayments\CoinPayments\Helper
 */
class Data extends AbstractHelper
{


    const FIAT_TYPE = 'fiat';
    const INVOICE_CACHE_PREFIX = 'CoinpaymentsInvoice';

    const XML_PATH_CONFIG_COINPAYMENTS = 'payment/coin_payments/';
    const XML_PATH_CONFIG_GENERAL = 'general/';

    const CLIENT_ID_KEY = 'client_id';
    const CLIENT_SECRET_KEY = 'client_secret';
    const CLIENT_WEBHOOKS_KEY = 'webhooks';
    const CLIENT_ORDER_STATUS_KEY = 'status_order_paid';
    const PACKAGE_VERSION = 'package_version';

    const API_WEBHOOK_ACTION = 'merchant/clients/%s/webhooks';
    const API_SIMPLE_INVOICE_ACTION = 'invoices';
    const API_MERCHANT_INVOICE_ACTION = 'merchant/invoices';

    const VALIDATE_SIMPLE_URL = 'coinpayments/validation/invoice';
    const VALIDATE_MERCHANT_URL = 'coinpayments/validation/webhook';

    const CREATE_INVOICE_URL = 'coinpayments/invoice/create';
    const WEBHOOK_NOTIFICATION_URL = 'coinpayments/webhooks/notification';

    const PAID_EVENT = 'Paid';
    const CANCELLED_EVENT = 'Cancelled';

    /**
     * @var Config
     */
    protected $resourceConfig;
    protected $baseConf;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Config $resourceConfig
     */
    public function __construct(Context $context,
                                ConfigInterface $resourceConfig)
    {
        parent::__construct($context);
        $this->resourceConfig = $resourceConfig;
        $this->baseConf = $this->getBaseConfig();
    }

    /**
     * @param $param
     * @return mixed
     */
    public function getConfig($param)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CONFIG_COINPAYMENTS . $param);
    }

    /**
     * @param $param
     * @return mixed
     */
    public function getGeneralConfig($param)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CONFIG_GENERAL . $param);
    }

    /**
     * @param $param
     * @param $value
     * @return mixed
     */
    public function setConfig($param, $value)
    {
        return $this->resourceConfig->saveConfig(self::XML_PATH_CONFIG_COINPAYMENTS . $param, $value);
    }

    /**
     * @return mixed
     */
    public function getBaseConfig()
    {
        return $this->scopeConfig->getValue('coinpayment/conf');
    }

    /**
     * @param $action
     * @return string
     */
    public function getApiUrl($action)
    {
        return sprintf('%s/api/v%s/%s', $this->baseConf['api_host'], $this->baseConf['api_version'], $action);
    }

    /**
     * @param $param
     * @return mixed
     */
    public function getBaseConfigParam($param)
    {
        $value = null;
        if (isset($this->baseConf[$param])) {
            $value = $this->baseConf[$param];
        }
        return $value;
    }

    /**
     * @param string $route
     * @return string
     */
    public function getHostUrl($route = '')
    {
        return $this->_getUrl($route, ['_direct' => null]);
    }

    /**
     * @param $clientId
     * @param $event
     * @return string
     */
    public function getNotificationUrl($clientId, $event)
    {
        $query = http_build_query(array(
            'event' => $event,
            'clientId' => $clientId,
        ));
        return $this->getHostUrl(DATA::WEBHOOK_NOTIFICATION_URL) . '?' . $query;
    }

    /**
     * @param $coinInvoiceId
     * @param $successUrl
     * @param $cancelUrl
     * @return string
     */
    public function getCoinCheckoutRedirectUrl($coinInvoiceId, $successUrl, $cancelUrl)
    {
        return sprintf(
            '%s/checkout/?invoice-id=%s&success-url=%s&cancel-url=%s',
            $this->baseConf['checkout_host'],
            $coinInvoiceId,
            $successUrl,
            $cancelUrl
        );
    }
}
