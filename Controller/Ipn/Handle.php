<?php

namespace Coinpayments\CoinPayments\Controller\Ipn;

use Coinpayments\CoinPayments\Model\Ipn;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Handle extends Action implements CsrfAwareActionInterface
{

    protected $_orderModel;

    protected $_ipnModel;

    protected $_jsonResultFactory;

    public function __construct(
        Context $context,
        Order $orderModel,
        Ipn $ipnModel,
        JsonFactory $jsonResultFactory
    )
    {
        parent::__construct($context);
        $this->_orderModel = $orderModel;
        $this->_ipnModel = $ipnModel;
        $this->_jsonResultFactory = $jsonResultFactory;

    }

    public function execute()
    {
        $requestData = (object)$this->getRequest()->getParams();
        $hmac = $this->getRequest()->getHeaders()->get('HMAC')->getFieldValue();

        $result = $this->_jsonResultFactory->create();

        if (!$requestData || !$hmac) {
            $result->setData([
                'error' => __('Invalid Data Sent')
            ]);
            return $result;
        }

        $errors =  $this->_ipnModel->processIpnRequest($requestData, $hmac);

        if (!empty($errors)) {
            $result->setData($errors);
            return $result;
        }
        
        return $result->setData(['error' => 'OK']);
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
