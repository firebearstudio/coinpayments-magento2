<?php

namespace Coinpayments\CoinPayments\Model\Config\Source;

use Coinpayments\CoinPayments\Model\Api\Rate;
use Magento\Framework\Option\ArrayInterface;

class Currency implements ArrayInterface
{
    const UNDEFINED_OPTION_LABEL = '-- Please Select --';

    /* @var Rate */
    protected $_coinpaymentsRate;

    /**
     * Currency constructor.
     * @param Rate $coinpaymentsRate
     */
    public function __construct(Rate $coinpaymentsRate)
    {
        $this->_coinpaymentsRate = $coinpaymentsRate;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $response = $this->_coinpaymentsRate->fetchRates(true, true);
        $currencies = [];
        $currencies[] = [
            'value' => '',
            'label' => self::UNDEFINED_OPTION_LABEL
        ];

        if ($response['error'] == 'ok') {
            foreach ($response['result'] as $key => $item) {
                $currencies[] = [
                    'value' => $key,
                    'label' => $item['name'],
                ];
            }
        } else {
            $currencies[] = ['value' => '', 'label' => $response['error']];
        }
        return $currencies;
    }
}
