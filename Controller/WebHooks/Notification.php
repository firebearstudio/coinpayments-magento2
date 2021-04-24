<?php

namespace Coinpayments\CoinPayments\Controller\WebHooks;

use Coinpayments\CoinPayments\Helper\Data;
use Coinpayments\CoinPayments\Model\WebHook;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Url;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\Result\JsonFactory;

class Notification extends Action implements CsrfAwareActionInterface
{
    /**
     * @var WebHook
     */
    protected $webHookModel;
    /**
     * @var Url
     */
    protected $urlBuilder;

    /* @var ScopeConfigInterface */
    protected $scopeConfig;
    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        Context $context,
        WebHook $webHookModel,
        Data $helper,
        Url $urlBuilder,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->webHookModel = $webHookModel;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!empty($this->helper->getConfig(Data::CLIENT_WEBHOOKS_KEY)) && !empty($this->getRequest()->getHeaders()->get('X-CoinPayments-Signature'))) {
            $content = $this->getRequest()->getContent();
            $signature = $this->getRequest()->getHeaders()->get('X-CoinPayments-Signature')->getFieldValue();
            $requestData = json_decode($content, true);
            if ($this->checkDataSignature($signature, $content)) {
                $this->webHookModel->receiveNotification($requestData);
            }
        }

    }

    /**
     * @param $signature
     * @param $content
     * @return bool
     */
    protected function checkDataSignature($signature, $content)
    {

        $requestUrl = $this->urlBuilder->getCurrentUrl();
        $clientSecret = $this->helper->getConfig(Data::CLIENT_SECRET_KEY);
        $encodedPure = $this->webHookModel->generateHmac([$requestUrl, $content], $clientSecret);
        return $signature == $encodedPure;
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
