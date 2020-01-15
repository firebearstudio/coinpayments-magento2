<?php

namespace Coinpayments\CoinPayments\Controller\Adminhtml\Validation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Url;
use Magento\Framework\Controller\Result\JsonFactory;
use Coinpayments\CoinPayments\Model\WebHook;
use Coinpayments\CoinPayments\Model\Invoice;

/**
 * Class Validation
 * @package Coinpayments\CoinPayments\Controller\Adminhtml\Validation
 */
class Validation extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var Url
     */
    protected $urlBuilder;

    /**
     * @var WebHook
     */
    protected $webHookModel;

    /**
     * @var Invoice
     */
    protected $invoiceModel;

    /**
     * Authenticate constructor.
     * @param Context $context
     * @param JsonFactory $jsonResultFactory
     * @param Url $urlBuilder
     * @param WebHook $webHook
     * @param Invoice $invoice
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        Url $urlBuilder,
        WebHook $webHook,
        Invoice $invoice
    )
    {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->webHookModel = $webHook;
        $this->invoiceModel = $invoice;
    }

    public function execute()
    {

    }
}