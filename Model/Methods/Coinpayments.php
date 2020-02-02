<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Coinpayments\CoinPayments\Model\Methods;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class Coinpayments
 * @package Coinpayments\CoinPayments\Model\Methods
 */
class Coinpayments extends AbstractMethod
{

    /**
     * Method code
     */
    const CODE = 'coin_payments';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)(int)$this->getConfigData('active', $storeId);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }
}
