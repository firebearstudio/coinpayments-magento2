<?php

namespace Coinpayments\CoinPayments\Controller\Adminhtml\Validation;

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

//        $clientSecret = 'secret';
//        $signatureString = "﻿POSTurlid{\"notificationsUrl\":\"notif-url\",\"notifications\":[2,4]}";
//        $signatureExample = base64_encode(hash_hmac('sha256', $signatureString, $clientSecret, true));

//        $signatureData = [
//            'POST',
//            'url',
//            'id',
//            json_encode(
//                json_encode([
//                    'notificationsUrl' => 'notif-url',
//                    "notifications" => [
//                        2,
//                        4
//                    ],
//                ])
//            )
//        ];
//
//        $equal = sprintf('%s%s%s%s', chr(239), chr(187), chr(191), implode('', $signatureData)) == $signatureString;
//
//        $imploded = implode('', $signatureData);
//        $signature = $this->webHookModel->generateHmac($signatureData, 'secret');

        $params = $this->getRequest()->getParams();
        $response = [];

        if (!empty($params['client_id']) && !empty($params['client_secret'])) {

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
                    $webHook = $this->webHookModel->createWebHook($client_id, $client_secret);
                    if (!empty($webHook)) {
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
                'success' => false,
                'errorText' => sprintf('Enter Coinpaymnets.NET credentials!'),
            ];
        }


        $result = $this->jsonResultFactory->create();
        $result->setData($response);
        return $result;
    }
}