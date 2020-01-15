<?php

namespace Coinpayments\CoinPayments\Controller\Invoice;

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

    public function __construct(
        Context $context,
        Invoice $invoice,
        \Magento\Checkout\Model\Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        Url $urlBuilder,
        JsonFactory $jsonResultFactory
    )
    {

        $this->urlBuilder = $urlBuilder;
        $this->invoiceModel = $invoice;
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

            $coinInvoiceCacheId = Invoice::INVOICE_CACHE_PREFIX . $order->getIncrementId();
            $coinInvoiceId = $this->checkoutSession->{'get' . $coinInvoiceCacheId}();

            if (empty($coinInvoiceId)) {

                $currencyCode = $order->getBaseCurrencyCode();
                $coinCurrency = $this->getCoinCurrency($currencyCode);
                $amount = number_format($order->getGrandTotal(), $coinCurrency['decimalPlaces'], '', '');

                if (!empty($coinCurrency)) {

                    $clientId = $this->scopeConfig->getValue('payment/coin_payments' . DIRECTORY_SEPARATOR . 'client_id');
                    $clientSecret = $this->scopeConfig->getValue('payment/coin_payments' . DIRECTORY_SEPARATOR . 'client_secret');
                    $merchantWebHooks = $this->scopeConfig->getValue('payment/coin_payments' . DIRECTORY_SEPARATOR . 'webhooks');

                    if ($merchantWebHooks) {
                        $invoiceData = $this->invoiceModel->createMerchant($clientId, $clientSecret, $coinCurrency['id'], $order->getIncrementId(), intval($amount));
                    } else {
                        $invoiceData = $this->invoiceModel->createSimple($clientId, $coinCurrency['id'], $order->getIncrementId(), intval($amount));
                    }

                    if (!empty($invoiceData['id'])) {
                        $this->checkoutSession->{'set' . $coinInvoiceCacheId}($invoiceData['id']);
                        $coinInvoiceId = $invoiceData['id'];
                    }
                }

            }

            if (!empty($coinInvoiceId)) {
                $response['coinInvoiceId'] = $coinInvoiceId;
                $response['redirectUrl'] = $this->getCoinCheckoutRedirectUrl($coinInvoiceId, $response['successUrl'], $response['cancelUrl']);
            }
        }

        $result = $this->jsonResultFactory->create();
        $result->setData($response);
        return $result;
    }

    /**
     * @param $coinInvoiceId
     * @param $successUrl
     * @param $cancelUrl
     * @return string
     */
    protected function getCoinCheckoutRedirectUrl($coinInvoiceId, $successUrl, $cancelUrl)
    {
        return sprintf('%s/checkout/?invoice-id=%s&success-url=%s&cancel-url=%s', $this->invoiceModel->getBaseConfig('api_url'), $coinInvoiceId, $successUrl, $cancelUrl);
    }

    /**
     * @param string $name
     * @return array|mixed
     */
    protected function getCoinCurrency(string $name)
    {

        $params = [
            'types' => Invoice::FIAT_TYPE,
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
