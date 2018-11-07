<?php

namespace Coinpayments\CoinPayments\Controller\Checkout;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class Failure extends \Magento\Framework\App\Action\Action
{
    private $checkoutSession;
    private $orderFactory;
    private $orderRepository;
    private $resultPageFactory;
    private $logger;
    private $cacheTypeList;
    private $cacheFrontendPool;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        OrderRepository $orderRepository,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        LoggerInterface $logger,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context);
    }


    public function execute()
    {
        $lastOrderId = $this->getRealOrderId();
        $order       = $this->orderRepository->get($lastOrderId);
        $order->setStatus(Order::STATE_CANCELED)->setState(Order::STATE_CANCELED);
        $this->orderRepository->save($order);
        $this->logger->info('ORDER INFO: ' . $order->getStatus() . $order->getState());

        return $this->resultPageFactory->create();
    }


    // Use this method to get ID    
    public function getRealOrderId()
    {
        $lastorderId = $this->checkoutSession->getLastOrderId();

        return $lastorderId;
    }

    public function getOrder()
    {
        if ($this->checkoutSession->getLastOrderId()) {
            $order = $this->orderFactory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());

            return $order;
        }

        return false;
    }
}
