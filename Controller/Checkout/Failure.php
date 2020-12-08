<?php

namespace Coinpayments\CoinPayments\Controller\Checkout;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;

class Failure extends Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Failure constructor.
     * @param Session $checkoutSession
     * @param OrderRepository $orderRepository
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepository $orderRepository,
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $lastOrderId = $this->getRealOrderId();
        $order = $this->orderRepository->get($lastOrderId);
        $order->setStatus(Order::STATE_CANCELED)->setState(Order::STATE_CANCELED);
        $this->orderRepository->save($order);
        return $this->resultPageFactory->create();
    }

    public function getRealOrderId()
    {
        return $this->checkoutSession->getLastOrderId();
    }

}
