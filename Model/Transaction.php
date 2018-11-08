<?php
namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Api\TransactionInterface;
use Coinpayments\CoinPayments\Logger\Logger;
use Coinpayments\CoinPayments\Model\Methods\Coinpayments;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;

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
    /* @var Coinpayments */
    protected $_coinpaymentsModel;
    /* @var TransactionBuilder */
    protected $_transactionBuilder;
    /* @var Logger */
    protected $_logger;

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
        UrlInterface $urlBuilder,
        Coinpayments $coinpaymentsModel,
        BuilderInterface $transactionBuilder,
        Logger $logger
    )
    {
        $this->_quoteFactory = $quoteFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_curl = $curl;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderModel = $orderModel;
        $this->_urlBuilder = $urlBuilder;
        $this->_coinpaymentsModel = $coinpaymentsModel;
        $this->_transactionBuilder = $transactionBuilder;
        $this->_logger = $logger;
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
            'ipn_url' => $this->_urlBuilder->getUrl('coinpayments/ipn/handle')
        ];

        $this->_curl->addHeader('HMAC', hash_hmac('sha512', http_build_query($data), $secretKey));
        $this->_curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->_curl->post($coinpaymentsApi, $data);
        $response = json_decode($this->_curl->getBody());

        if ($response->error == 'OK') {
            $this->addTransactionToOrder($order, $response);
        }

        return $response;
    }

    public function addTransactionToOrder(Order $order, $paymentData) {
        try {
            $payment = $order->getPayment();
            $payment->setMethod($this->_coinpaymentsModel->getCode());
            $payment->setLastTransId($paymentData['txn_id']);
            $payment->setTransactionId($paymentData['txn_id']);
            $payment->setAdditionalInformation([Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]);

            $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());

            /* @var BuilderInterface */
            $transaction = $this->_transactionBuilder
                ->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['txn_id'])
                ->setAdditionalInformation([Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData])
                ->setFailSafe(true)
                ->build(Order\Payment\Transaction::TYPE_CAPTURE);

            // Add transaction to payment
            $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
            $payment->setParentTransactionId(null);

            // Save payment, transaction and order
            $payment->save();
            $order->save();
            $transaction->save();

            return  $transaction->getTransactionId();

        } catch (\Exception $e) {
            $this->_logger->info("Create transaction error. OrderId: " . $order->getId() . "\n. Message: " . $e->getMessage());
        }
    }
}