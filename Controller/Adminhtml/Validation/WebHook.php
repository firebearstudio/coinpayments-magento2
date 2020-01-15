<?php

namespace Coinpayments\CoinPayments\Controller\Adminhtml\Validation;

use Coinpayments\CoinPayments\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**1
 * Class WebHook
 * @package Coinpayments\CoinPayments\Controller\Adminhtml\Validation
 */
class WebHook extends Validation
{

    public function execute()
    {

        $params = $this->getRequest()->getParams();
        $response = [];

        if (!empty($params['client_id']) && !empty($params['client_secret']) && $this->helper->getConfig('validated') != ($params['client_id'] . $params['client_secret'])) {

            $client_id = $params['client_id'];
            $client_secret = $params['client_secret'];

            $webHooksList = $this->webHookModel->getList($client_id, $client_secret);

            if (!empty($webHooksList)) {

                $webHooksUrlsList = [];
                if (!empty($webHooksList['items'])) {
                    $webHooksUrlsList = array_map(function ($webHook) {
                        return $webHook['notificationsUrl'];
                    }, $webHooksList['items']);
                }

                if (!in_array($this->webHookModel->getWebHookCallbackUrl(), $webHooksUrlsList)) {
                    $webHook = $this->webHookModel->createWebHook($client_id, $client_secret, $this->webHookModel->getWebHookCallbackUrl());
                    if (!empty($webHook)) {
                        $this->helper->setConfig('validated', $params['client_id'] . $params['client_secret']);
                        $response = [
                            'success' => $webHook,
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'errorText' => sprintf('Failed to create WebHook!'),
                        ];
                    }
                } else {
                    $this->helper->setConfig('validated', $params['client_id'] . $params['client_secret']);
                    $response = [
                        'success' => true,
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'errorText' => sprintf('Failed to get WebHooks list!'),
                ];
            }
        } elseif ($this->helper->getConfig('validated') == ($params['client_id'] . $params['client_secret'])) {
            $response = [
                'success' => true,
            ];
        } else {
            $response = [
                'success' => false,
                'errorText' => sprintf('Enter Coinpaymnets.NET credentials!'),
            ];
        }


        $result = $this->jsonResultFactory->create();
        $result->setData($response);
        return $result;
    }
}