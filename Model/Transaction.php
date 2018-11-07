<?php
namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Api\TransactionInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;

class Transaction implements TransactionInterface
{
    /* @var \Magento\Quote\Model\QuoteFactory */
    protected $_quoteFactory;
    /* @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;
    /* @var Order */
    protected $_orderModel;
    /* @var Curl */
    protected $_curl;
    /* @var ScopeConfigInterface */
    protected $_scopeConfig;
    /* @var UrlInterface */
    protected $_urlBuilder;

    /**
     * Transaction constructor.
     * @param QuoteFactory $quoteFactory
     * @param Session $checkoutSession
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param Order $orderModel
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        Session $checkoutSession,
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        Order $orderModel,
        UrlInterface $urlBuilder
    )
    {
        $this->_quoteFactory = $quoteFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_curl = $curl;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderModel = $orderModel;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @param string $cartId
     * @param string $currency
     * @param float $value
     * @return boolean
     */
    public function createTransaction($cartId, $currency, $value)
    {
        $quote = $this->_quoteFactory->create()->load($cartId);
        $order = $this->_checkoutSession->getLastRealOrder();

        if (!$order->getId() && !$quote->getId()) {
            return false;
        }
        $data = $this->prepareCreateTransactionData($order, $currency);
        $this->_checkoutSession->setCoinpaymentsCurrency($currency);
        $this->_checkoutSession->setCoinpaymentsCurrencyTotal($value);
        $this->_checkoutSession->setCoinpaymentsTransactionData(json_encode($data));
        return true;
    }

    /**
     * @param $order
     * @return mixed
     */
    private function prepareCreateTransactionData($order, $currenctCurrency)
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $secretKey = $this->_scopeConfig->getValue('payment/coin_payments/secret_key', $scope);
        $publicKey = $this->_scopeConfig->getValue('payment/coin_payments/public_key', $scope);
        $coinpaymentsApi = $this->_scopeConfig->getValue('coinpayment/conf/api_payment', $scope);

        $data = [
            'version' => 1,
            'cmd' => 'create_transaction',
            'key' => $publicKey,
            'amount' => $order->getBaseGrandTotal(),
            'currency1' => $order->getOrderCurrencyCode(),
            'currency2' => $currenctCurrency,
            'buyer_email' => $order->getCustomerEmail(),
            'buyer_name' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
            'invoice' => $order->getIncrementId(),
            'ipn_url' => $this->_urlBuilder->getUrl('coinpayments/ipn/index')
        ];

        $this->_curl->addHeader('HMAC', hash_hmac('sha512', http_build_query($data), $secretKey));
        $this->_curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->_curl->post($coinpaymentsApi, $data);
        $response = json_decode($this->_curl->getBody());

        return $response;
    }
}