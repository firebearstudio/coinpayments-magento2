<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Coinpayments\CoinPayments\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session;

class Status extends \Magento\Framework\View\Element\Template
{
    /* @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    /**
     * Status constructor.
     * @param Session $checkoutSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Session $checkoutSession,
        Template\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @var string
     */
    protected $_template = 'Coinpayments_CoinPayments::coinpayments/status.phtml';

    public function getTransactionData()
    {
        if ($this->_checkoutSession->getCoinpaymentsTransactionData()) {
            return json_decode($this->_checkoutSession->getCoinpaymentsTransactionData());
        }
        return (object) [
            'error' => __('Transaction is not found')
        ];
    }

    /**
     * @return mixed
     */
    public function getTransactionCurrency()
    {
        return $this->_checkoutSession->getCoinpaymentsCurrency();
    }

    /**
     * @return string
     */
    public function getLastOrderIncrementId()
    {
        return $this->_checkoutSession->getLastRealOrder()->getIncrementId();
    }
}
