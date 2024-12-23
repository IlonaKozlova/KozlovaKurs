<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'language' => 'ru',
    'id' => 'basic',
    // PKGH {
    'name'=>'shop',
    'language'=>'ru-RU',
    // }
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            // PKGH {
            'cookieValidationKey' => 'Ilona',
            'parsers' => [
            'application/json' => 'yii\web\JsonParser',]
            // }
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        // PKGH {
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                //['class' => 'yii\rest\UrlRule', 'controller' => ['user', 'service', 'zapis']],

                // Пользователи
                'POST register' => 'user/create', // Регистрация
                'POST login' => 'user/login', // Авторизация
                'GET profile' => 'user/profile', // Просмотр профиля
                'PATCH profile' => 'user/update', // Обновление профиля //
                'POST logout' => 'user/logout', // Выход из аккаунта
                'GET user' => 'user/index',

                // Услуги
                'GET services' => 'service/index', // Получение списка услуг
                'POST services' => 'service/create', // Добавление новой услуги

                // Записи
                'GET user/zapis' => 'user/zapis', // Просмотр всех записей (администратор)
                'PATCH zapis' => 'zapis/update', // Изменение записей
                'POST zapis' => 'zapis/create', // Создание записи клиента
                'DELETE zapis' => 'zapis/delete', // Отмена записи клиента

                // Просмотр записей клиентов
                'GET admin/zapis' => 'user/zapis/adminzapisi',
            ],
        ]
        // }
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        // PKGH {
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
        // }
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        // PKGH {
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
        // }
    ];
}

return $config;
