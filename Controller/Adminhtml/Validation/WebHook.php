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

        if (!empty($params['client_id']) && !empty($params['client_secret'])) {
            if ($this->helper->getConfig('validated') != ($params['client_id'] . $params['client_secret'])) {

                $clientId = $params['client_id'];
                $clientSecret = $params['client_secret'];

                $webHooksList = $this->webHookModel->getList($clientId, $clientSecret);

                if (!empty($webHooksList)) {

                    $webHooksUrlsList = [];
                    if (!empty($webHooksList['items'])) {
                        $webHooksUrlsList = array_map(function ($webHook) {
                            return $webHook['notificationsUrl'];
                        }, $webHooksList['items']);
                    }

                    if (!in_array($this->helper->getHostUrl(DATA::WEBHOOK_NOTIFICATION_URL), $webHooksUrlsList)) {
                        $webHook = $this->webHookModel->createWebHook($clientId, $clientSecret, $this->helper->getHostUrl(DATA::WEBHOOK_NOTIFICATION_URL));
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
            } else {
                $response = [
                    'success' => true,
                ];
            }
        } else {
            $response = [
                'success' => false,
                'errorText' => sprintf('Enter Coinpayments.NET credentials!'),
            ];
        }


        $result = $this->jsonResultFactory->create();
        $result->setData($response);
        return $result;
    }
}