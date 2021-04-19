<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Coinpayments\CoinPayments\Controller\Iframe;

class Index extends \Magento\Framework\App\Action\Action
{
    private $configResource;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $cart;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context                     $context
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $config
     * @param \Magento\Checkout\Model\Cart                              $cart
     * @param \Magento\Quote\Model\QuoteFactory                         $quoteFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\MutableScopeConfigInterface $config,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->config       = $config;
        $this->cart         = $cart;
        $this->quoteFactory = $quoteFactory;
        $this->scopeConfig  = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @route coinpayments/iframe/index
     */
    public function execute()
    {
        $coinpaymentUrl = $this->scopeConfig->getValue(
            'coinpayment/conf/url_payment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $html           = 'You will be transfered to <a href="'.$coinpaymentUrl.'" target="_blank\">CoinPayments.net</a> to complete your purchase when using this payment method.';
        $this->getResponse()->setBody(json_encode(['html' => $html]));
    }
}
