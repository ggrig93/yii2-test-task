<?php

namespace app\controllers;

use app\dto\TeletypeRequestDto;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use Yii;
use app\components\TeletypeService;

class TeletypeController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    /**
     * @return void
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionWebhook(): void
    {
        $data = Yii::$app->request->post();
        if (!$data) {
            Yii::error('Missing data from teletype webhook', 'teletype');
            return;
        }
        $payload = json_decode($data['payload'], true);
        $dto = new TeletypeRequestDto($data['name'], $data['payload'], $payload['message']);

        $teletypeService = new TeletypeService();
        $teletypeService->sendRequest($dto->name, 'POST', $dto);
    }
}
