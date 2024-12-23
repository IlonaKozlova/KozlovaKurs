<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\Services;

class ServiceController extends Controller
{
    public $modelClass = 'app\models\Services';

    // Список услуг
    public function actionIndex()
    {
        $services = Services::find()->all();

        if ($services) {
            return $services;
        }

        throw new \yii\web\NotFoundHttpException("Услуги не найдены.");
    }

    // Добавление услуги
    public function actionCreate()
    {
        $data = Yii::$app->request->post();
        $services = new Services();
        $services->attributes = $data;

        // Проверка уникальности имени услуги и сохранение
        if ($services->validate() && $services->save()) {
            return $services;
        }

        return [
            'error' => [
                'code' => 422,
                'message' => 'Validation error',
                'errors' => $services->errors,
            ],
        ];
    }
}
