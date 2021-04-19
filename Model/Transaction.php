<?php

namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Api\TransactionInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Session;
use Coinpayments\CoinPayments\Model\Api\Transaction as TransactionApi;

class Transaction implements TransactionInterface
{
    /* @var \Magento\Quote\Model\QuoteFactory */
    protected $_quoteFactory;
    /* @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;
    /* @var TransactionApi */
    protected $_apiTransaction;

    /**
     * Transaction constructor.
     * @param QuoteFactory $quoteFactory
     * @param Session $checkoutSession
     * @param TransactionApi $apiTransaction
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        Session $checkoutSession,
        TransactionApi $apiTransaction
    )
    {
        $this->_quoteFactory = $quoteFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_apiTransaction = $apiTransaction;
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
        $data = $this->_apiTransaction->create($order, $currency);
        $this->_checkoutSession->setCoinpaymentsCurrency($currency);
        $this->_checkoutSession->setCoinpaymentsCurrencyTotal($value);
        $this->_checkoutSession->setCoinpaymentsTransactionData(json_encode($data));
        return true;
    }
}