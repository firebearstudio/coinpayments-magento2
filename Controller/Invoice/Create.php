<?php

namespace Coinpayments\CoinPayments\Controller\Invoice;

use Coinpayments\CoinPayments\Helper\Data;
use Coinpayments\CoinPayments\Model\Invoice;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Url;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\Result\JsonFactory;

class Create extends Action implements CsrfAwareActionInterface
{

    /**
     * @var Invoice
     */
    protected $invoiceModel;
    /**
     * @var Url
     */
    protected $urlBuilder;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Create constructor.
     * @param Context $context
     * @param Invoice $invoice
     * @param Data $helper
     * @param Session $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Url $urlBuilder
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        Context $context,
        Invoice $invoice,
        Data $helper,
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        Url $urlBuilder,
        JsonFactory $jsonResultFactory
    )
    {

        $this->urlBuilder = $urlBuilder;
        $this->invoiceModel = $invoice;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $response = [
            'successUrl' => $this->urlBuilder->getUrl('checkout/onepage/success'),
            'cancelUrl' => $this->urlBuilder->getUrl('coinpayments/checkout/failure'),
        ];

        $order = $this->checkoutSession->getLastRealOrder();

        if ($order->getIncrementId()) {

            $coinInvoiceCacheId = Data::INVOICE_CACHE_PREFIX . $order->getIncrementId();
            $coinInvoiceId = $this->checkoutSession->{'get' . $coinInvoiceCacheId}();

            if (empty($coinInvoiceId)) {

                $currencyCode = $order->getBaseCurrencyCode();
                $coinCurrency = $this->getCoinCurrency($currencyCode);
                $amount = number_format($order->getGrandTotal(), $coinCurrency['decimalPlaces'], '', '');

                if (!empty($coinCurrency)) {

                    $clientId = $this->helper->getConfig(Data::CLIENT_ID_KEY);
                    $clientSecret = $this->helper->getConfig(Data::CLIENT_SECRET_KEY);
                    $merchantWebHooks = $this->helper->getConfig(Data::CLIENT_WEBHOOKS_KEY);
                    $invoiceId = sprintf('%s|%s', $order->getId(), $order->getPayment()->getId());

                    if ($merchantWebHooks) {
                        $invoicesData = $this->invoiceModel->createMerchant($clientId, $clientSecret, $coinCurrency['id'], $invoiceId, intval($amount), $order->getGrandTotal());
                        $invoiceData = array_shift($invoicesData);
                    } else {
                        $invoiceData = $this->invoiceModel->createSimple($clientId, $coinCurrency['id'], $invoiceId, intval($amount), $order->getGrandTotal());
                    }

                    if (!empty($invoiceData['id'])) {
                        $this->checkoutSession->{'set' . $coinInvoiceCacheId}($invoiceData['id']);
                        $coinInvoiceId = $invoiceData['id'];
                        $this->invoiceModel->createOrderTransaction($order, $coinInvoiceId);
                    }
                }

            }

            if (!empty($coinInvoiceId)) {
                $response['coinInvoiceId'] = $coinInvoiceId;
                $response['redirectUrl'] = $this->helper->getCoinCheckoutRedirectUrl($coinInvoiceId, $response['successUrl'], $response['cancelUrl']);
            }
        }

        $result = $this->jsonResultFactory->create();
        $result->setData($response);
        return $result;
    }

    /**
     * @param string $name
     * @return array|mixed
     */
    protected function getCoinCurrency(string $name)
    {

        $params = [
            'types' => Data::FIAT_TYPE,
            'q' => $name,
        ];
        $items = [];

        $listData = $this->invoiceModel->getCurrencies($params);
        if (!empty($listData['items'])) {
            $items = $listData['items'];
        }

        return array_shift($items);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

}
