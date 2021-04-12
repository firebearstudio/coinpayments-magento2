<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Coinpayments\CoinPayments\Block;

/**
 * Base payment iformation block
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Coinpayments_CoinPayments::coinpayments/info/default.phtml';

    /**
     * @return mixed
     */
    public function getCoinPaymentsInvoiceUrl()
    {
        return true;
    }
}
