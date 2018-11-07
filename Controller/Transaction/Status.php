<?php
/**
 * @copyright: Copyright © 2018 Dedicated lab. All rights reserved.
 * @author   : Dedicated lab <pvajda123qwe@gmail.com>
 */

namespace Coinpayments\CoinPayments\Controller\Transaction;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Status extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Status constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $page = $this->_resultPageFactory->create();
        $page->getConfig()->getTitle()->set(__('Transaction Status'));
        return $page;
    }
}
