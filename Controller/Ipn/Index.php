<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\CoinPayments\Controller\Ipn;

use Magento\Sales\Model\Order;
use Magento\Framework\App\Action\Context;
use Firebear\CoinPayments\Logger\Logger;
use Firebear\CoinPayments\Helper\Data as CoinPaymentHelper;

/**
 * Class Index
 *
 * @package Firebear\CoinPayments\Controller\Ipn
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var Logger
     */
    private $log;

    /**
     * @var CoinPaymentHelper
     */
    private $helper;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param Logger $logger
     * @param CoinPaymentHelper $helper
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        Logger $logger,
        CoinPaymentHelper $helper
    ) {
        $this->orderRepository = $orderRepository;
        $this->log = $logger;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->getParams()) {
            if ($this->is_ipn_valid()) {
                // Payment was successful, so update the order's state, send order email and move to the success page
                $order_id = (int)$this->getRequest()->getParam('invoice');
                if ($order = $this->orderRepository->get($order_id)) {
                    if ($order->getState() == Order::STATE_NEW) {
                        if ($this->getRequest()->getParam('ipn_type') == 'button') {
                            if ($this->getRequest()->getParam('currency1') == $order->getBaseCurrencyCode()) {
                                if ($this->getRequest()->getParam('amount1') >= $order->getBaseGrandTotal()) {
                                    $status = (int)$this->getRequest()->getParam('status');
                                    $this->log->info("STATUS: " . $status);
                                    $this->checkStatus($status, $order);
                                    $order->save();

                                    return 'IPN OK';
                                } else {
                                    $this->logAndDie('Amount paid is less than order total!', $order);
                                }
                            } else {
                                $this->logAndDie('Original currency does not match!', $order);
                            }
                        } else {
                            $this->logAndDie('Invalid IPN type!', $order);
                        }
                    } else {
                        $this->logAndDie('Order is no longer new. (most likely IPN has already been processed)');
                    }
                } else {
                    $this->logAndDie('Could not load order with ID: ' . $order_id);
                }
            }
        } else {
            $this->logAndDie('Request is EMPTY');
        }
    }

    /**
     * @param $status
     * @param $order
     */
    private function checkStatus($status, $order)
    {
        if ($status < 0) {
            //canceled or timed out
            $order->cancel();
            $order->setState(
                ORDER::STATE_CANCELED,
                true,
                'CoinPayments.net Payment Status: ' . $this->getRequest()->getParam(
                    'status_text'
                )
            )->setStatus(ORDER::STATE_CANCELED);
        } else {
            if ($status >= 100 || $status == 2) {
                //order complete or queued for nightly payout
                $str = 'CoinPayments.net Payment Status: ' . $this->getRequest()->geпtParam('status_text')
                    . '<br />';
                $str .= 'Transaction ID: ' . $this->getRequest()->getParam('txn_id')
                    . '<br />';
                $str .= 'Original Amount: ' . sprintf('%.08f', $this->getRequest()->getParam('amount1'))
                    . ' ' . $this->getRequest()->getParam('currency1') . '<br />';
                $str .= 'Received Amount: ' . sprintf('%.08f', $this->getRequest()->getParam('amount2'))
                    . ' ' . $this->getRequest()->getParam('currency2');
                $order->setState(
                    $this->helper->getGeneralConfig('status_order_paid'),
                    true,
                    $str
                )->setStatus($this->helper->getGeneralConfig('status_order_paid'));
            } else {
                //order pending
                $order->setState(
                    Order::STATE_NEW,
                    true,
                    'CoinPayments.net Payment Status: ' . $this->getRequest()->getParam(
                        'status_text'
                    )
                )->setStatus(Order::STATE_PROCESSING);
            }
        }
    }

    /**
     * @param      $msg
     * @param null $order
     */
    private function logAndDie($msg, $order = null)
    {
        if ($this->helper->getGeneralConfig('debug')) {
            $messsageString = '';
            if ($order !== null) {
                $messsageString = 'Order ID: ' . $order->getId() . '<br/>';
            }
            $messsageString .= $msg;
            $this->log->info($messsageString);
        }

        return;
    }

    /**
     * @return bool
     */
    private function is_ipn_valid()
    {
        $ipn = $this->getRequest()->getParams();
        if (!isset($ipn['ipn_mode'])) {
            $this->logAndDie('IPN received with no ipn_mode.');
        }
        if ($ipn['ipn_mode'] == 'hmac') {
            if ($this->checkHmacIpn($ipn)) {
                return true;
            }
        } else {
            if ($ipn['ipn_mode'] == 'httpauth' && $this->helper->getGeneralConfig('ipn_mode') == 1) {
                if ($this->checkHttpauthIpn($ipn)) {
                    return true;
                }
            } else {
                $this->logAndDie('Unknown ipn_mode.');
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function checkHmacIpn($ipn)
    {
        if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
            $this->logAndDie('No HMAC signature sent.');

            return false;
        }

        $request = file_get_contents('php://input');
        if ($request === false || empty($request)) {
            $this->logAndDie(
                'Error reading POST data: ' . print_r($_SERVER, true) . '/' . print_r(
                    $this->getRequest()->getParams(),
                    true
                )
            );

            return false;
        }

        $merchant = isset($ipn['merchant']) ? $ipn['merchant'] : '';
        if (empty($merchant)) {
            $this->logAndDie('No Merchant ID passed');

            return false;
        }
        if ($merchant != trim($this->helper->getGeneralConfig('merchant_id'))) {
            $this->logAndDie('Invalid Merchant ID');

            return false;
        }

        $hmac = hash_hmac("sha512", $request, trim($this->helper->getGeneralConfig('ipn_secret')));
        if ($hmac != $_SERVER['HTTP_HMAC']) {
            $this->logAndDie('HMAC signature does not match');

            return false;
        }

        return true;
    }

    private function checkHttpauthIpn($ipn)
    {
        if (isset($_SERVER['PHP_AUTH_USER'])
            && $_SERVER['PHP_AUTH_USER'] == trim(
                $this->helper->getGeneralConfig('merchant_id')
            )
        ) {
            if (isset($_SERVER['PHP_AUTH_PW'])
                && $_SERVER['PHP_AUTH_PW'] == trim(
                    $this->helper->getGeneralConfig('ipn_secret')
                )
            ) {
                $merchant = isset($ipn['merchant']) ? $ipn['merchant'] : '';
                if (empty($merchant)) {
                    $this->logAndDie('No Merchant ID passed');
                }
                if ($merchant != trim($this->helper->getGeneralConfig('merchant_id'))) {
                    $this->logAndDie('Invalid Merchant ID');
                }

                return true;
            } else {
                $this->logAndDie(
                    'IPN Secret not correct or no HTTP Auth variables passed. If you are using PHP in CGI mode try the HMAC method.'
                );
            }
        } else {
            $this->logAndDie(
                'Merchant ID not correct or no HTTP Auth variables passed. If you are using PHP in CGI mode try the HMAC method.'
            );
        }
        return false;
    }
}
