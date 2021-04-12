<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Coinpayments\CoinPayments\Controller\Invoice;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    private $configResource;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Coinpayments\CoinPayments\Helper\Data
     */
    private $coinPayemtnsHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $config
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Coinpayments\CoinPayments\Helper\Data $coinPayemtnsHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\MutableScopeConfigInterface $config,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Coinpayments\CoinPayments\Helper\Data $coinPayemtnsHelper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->config = $config;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->log = $logger;
        $this->orderRepository = $orderRepository;
        $this->coinPayemtnsHelper = $coinPayemtnsHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (empty($this->checkoutSession->getData('last_success_quote_id'))) {
            return $this->_redirect('checkout/cart');
        }

        $this->log->info('ORDER_ID: ' . $this->checkoutSession->getLastRealOrder()->getId());
        $this->_coreRegistry->register('last_success_quote_id', $this->checkoutSession->getData('last_success_quote_id'));
        $this->_coreRegistry->register('last_success_order_id', $this->checkoutSession->getLastRealOrder()->getId());
        $orderModel = $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());
        $orderModel->setState(Order::STATE_NEW)
            ->setStatus($this->coinPayemtnsHelper->getGeneralConfig('status_order_placed'));
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Pay with CoinPayments'));

        return $resultPage;
    }
}
