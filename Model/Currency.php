<?php
namespace Coinpayments\CoinPayments\Model;

use Coinpayments\CoinPayments\Api\CurrencyManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Session;

class Currency implements CurrencyManagementInterface
{
    /* @var \Magento\Quote\Model\QuoteFactory */
    protected $_quoteFactory;
    /* @var Session */
    protected $_checkoutSession;
    public function __construct(QuoteFactory $quoteFactory , Session $checkoutSession)
    {
        $this->_quoteFactory = $quoteFactory;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param string $cartId
     * @param string $currency
     * @param float $value
     * @return boolean
     */
    public function saveCurrency($cartId, $currency, $value)
    {
        $quote = $this->_quoteFactory->create()->load($cartId);
        if (!$quote->getId()) {
            return false;
        }

        $this->_checkoutSession->setCoinpaymentsCurrency($currency);
        $this->_checkoutSession->setCoinpaymentsCurrencyTotal($value);
        return true;
    }
}