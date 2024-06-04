<?php

namespace app\components;

use \app\dto\TeletypeRequestDto;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;

class TeletypeService extends Component
{
    /**
     * @param string $endpoint
     * @param string $method
     * @param TeletypeRequestDto $dto
     * @return void
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function sendRequest(string $endpoint, string $method, TeletypeRequestDto $dto): void
    {
        $client = new \yii\httpclient\Client();
        $request = $this->call($client, $method, $endpoint, [
            'name' => $dto->name,
            'payload' => $dto->payload,
        ]);

        if ($request->isOk) {
            $logType = 'teletype';
            $response = $this->call($client, "GET", 'messages', [
                'dialogId' => $dto->message['dialogId'],
                'pageSize' => 1
            ]);

            if ($response->isOk) {
                $content = $response->data['data']['items'] ? $response->data['data']['items'][0]['text'] : '';
            } else {
                Yii::error('Error Teletype API: ' . $request->content, 'teletype');
                return;
            }

            if ($endpoint === 'new message') {
                $logType = 'client';
                if (strpos($content, 'ping?') >= 0) {
                    $sendMessage = $this->call($client, "POST", 'messageSend', [
                        'dialogId' => $dto->message['dialogId'],
                        'text' => 'pong!'
                    ]);

                    if($sendMessage->isOk){
                        Yii::info('Operator send PONG!', 'operator');
                    }else{
                        Yii::error('Operator failed to send PONG!: ' . $sendMessage->content, 'teletype');
                    }
                }
            } elseif ($endpoint === 'success send') {
                $logType = 'operator';
            }

            Yii::info($content, $logType);
        } else {
            Yii::error('Error Teletype API: ' . $request->content, 'teletype');
        }
    }

    public function call($client, $method, $endpoint, $data)
    {
        return $client->createRequest()
            ->setMethod($method)
            ->setUrl(Yii::$app->params['teletypeApiUrl'] . Yii::$app->params['teletypeEndpoints'][$endpoint])
            ->setHeaders([
                'X-Auth-Token' => Yii::$app->params['teletypeAccessToken'],
                'Accept' => 'application/json',
            ])
            ->setFormat(Client::FORMAT_JSON)
            ->setData($data)
            ->send();
    }
}
