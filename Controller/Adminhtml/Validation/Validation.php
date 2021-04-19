<?php

namespace Coinpayments\CoinPayments\Controller\Adminhtml\Validation;

use Coinpayments\CoinPayments\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Coinpayments\CoinPayments\Model\WebHook;
use Coinpayments\CoinPayments\Model\Invoice;

/**
 * Class Validation
 * @package Coinpayments\CoinPayments\Controller\Adminhtml\Validation
 */
abstract class Validation extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var WebHook
     */
    protected $webHookModel;

    /**
     * @var Invoice
     */
    protected $invoiceModel;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Validation constructor.
     * @param Context $context
     * @param JsonFactory $jsonResultFactory
     * @param WebHook $webHook
     * @param Invoice $invoice
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        WebHook $webHook,
        Invoice $invoice,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->webHookModel = $webHook;
        $this->invoiceModel = $invoice;
        $this->helper = $helper;
    }

    abstract public function execute();
}