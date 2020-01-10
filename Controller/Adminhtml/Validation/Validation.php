<?php

namespace Coinpayments\CoinPayments\Controller\Adminhtml\Validation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Validation
 * @package Coinpayments\CoinPayments\Controller\Adminhtml\Validation
 */
class Validation extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var \Coinpayments\CoinPayments\Model\Api\WebHook
     */
    protected $webHookModel;

    /**
     * @var \Coinpayments\CoinPayments\Model\Api\Invoice
     */
    protected $invoiceModel;

    /**
     * Authenticate constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Coinpayments\CoinPayments\Model\Api\WebHook $webHook
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Coinpayments\CoinPayments\Model\Api\WebHook $webHook,
        \Coinpayments\CoinPayments\Model\Api\Invoice $invoice
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