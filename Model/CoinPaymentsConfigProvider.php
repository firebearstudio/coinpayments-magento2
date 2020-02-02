<?php

namespace Coinpayments\CoinPayments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * Class CoinPaymentsConfigProvider
 * @package Coinpayments\CoinPayments\Model
 */
class CoinPaymentsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * CoinPaymentsConfigProvider constructor.
     * @param Repository $assetRepo
     */
    public function __construct(Repository $assetRepo)
    {
        $this->_assetRepo = $assetRepo;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'coinpayments' => [
                    'logo' => $this->_assetRepo->getUrl('Coinpayments_CoinPayments::images/logo.png'),
                ]
            ]
        ];
    }
}
