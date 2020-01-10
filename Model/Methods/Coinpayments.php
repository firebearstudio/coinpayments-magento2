<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Coinpayments\CoinPayments\Model\Methods;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\Method\TransparentInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order;

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


    /* Uncomment if need using blocks

    protected $_formBlockType               = 'Coinpayments\CoinPayments\Block\Form\Coinpayments';

    protected $_infoBlockType               = 'Coinpayments\CoinPayments\Block\Info';*/
    

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
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

    public function getCode()
    {
        return $this->_code;
    }
}
