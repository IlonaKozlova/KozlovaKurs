<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\User;
use app\models\Zapis;
use app\models\LoginForm;
use yii\web\IdentityInterface;


class UserController extends Controller
{
    public $modelClass = 'app\models\User';

    // Регистрация
    public function actionCreate()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $data = Yii::$app->request->post();
    $user = new User();

    $user->attributes = $data;
    $user->password = Yii::$app->security->generatePasswordHash($user->password); // Хэшируем пароль
    $user->token = Yii::$app->security->generateRandomString(); // Генерация токена

    // Сохранение пользователя
    if ($user->validate() && $user->save()) {
        return [
            'status' => 'success',
            'message' => 'Регистрация прошла успешно',
        ];
    }

    return [
        'status' => 'error',
        'message' => 'Ошибка регистрации',
        'errors' => $user->errors,
    ];
}


    // Авторизация
    public function actionLogin()
    {
        // Устанавливаем формат ответа в JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request->post();

        // Валидация входных данных
        if (empty($request['login'])) {
            Yii::$app->response->statusCode = 422;
            return [
                'error' => [
                    'code' => 422,
                    'message' => 'Ошибка валидации',
                    'errors' => [
                        'login' => ['Логин обязателен'],
                    ],
                ],
            ];
        }

        if (empty($request['password'])) {
            Yii::$app->response->statusCode = 422;
            return [
                'error' => [
                    'code' => 422,
                    'message' => 'Ошибка валидации',
                    'errors' => [
                        'password' => ['Пароль обязателен'],
                    ],
                ],
            ];
        }

        // // Поиск пользователя по логину
        $user = User::findOne(['login' => $request['login']]);

        // Генерация нового токена
        $token = Yii::$app->getSecurity()->generateRandomString();

        // Обновление токена пользователя
        $user->token = $token;

        // Сохранение токена в базе данных
        if ($user->save(false)) {
            return [
                'status' => 'success',
                'message' => 'Авторизация прошла успешно',
                'data' => [
                    'token' => $token,
                ],
            ];
        }

        // Ошибка сохранения токена
        Yii::$app->response->statusCode = 500;
        return [
            'error' => [
                'code' => 500,
                'message' => 'Не удалось сохранить токен',
            ],
        ];
    }




    // Профиль
    public function actionProfile()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    // Получение токена из заголовка Authorization
    $token = Yii::$app->request->getHeaders()->get('Authorization');

    // Проверка, что токен существует и имеет правильный формат
    if (!$token || strpos($token, 'Bearer ') !== 0) {
        return [
            'status' => 'error',
            'message' => 'Токен отсутствует или некорректный формат. Пожалуйста, выполните вход.',
        ];
    }

    // Извлекаем токен без "Bearer "
    $token = substr($token, 7);

    // Поиск пользователя по токену
    $user = User::findOne(['token' => $token]);

    // Проверка, найден ли пользователь
    if (!$user) {
        Yii::error("Пользователь с токеном {$token} не найден.", 'user'); // Логирование ошибки
        return
        [
            'error' => [
                'code' => 404,
                'message' => 'Пользователь не найден или токен недействителен.',
                'status' => 'error',
            ],
        ];
    }

    // Возвращение данных профиля
    return [
        'status' => 'success',
        'data' => [
            'id' => $user->id,
            'login' => $user->login,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
        ],
    ];
}


    // Выход
    public function actionLogout()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    // Получение токена из заголовка Authorization
    $token = Yii::$app->request->getHeaders()->get('Authorization');

    // Проверка, что токен существует и имеет правильный формат
    if (!$token || strpos($token, 'Bearer ') !== 0) {
        return [
            'status' => 'error',
            'message' => 'Токен отсутствует или некорректный формат.',
        ];
    }

    // Извлекаем токен без "Bearer "
    $token = substr($token, 7);

    // Поиск пользователя по токену
    $user = User::findOne(['token' => $token]);

    // Проверка, найден ли пользователь
    if (!$user) {
        return [
            'status' => 'error',
            'message' => 'Пользователь не найден или токен недействителен.',
        ];
    }

    // Очистка токена пользователя
    $user->token = null;

    // Сохранение изменений
    if ($user->save(false)) {
        return [
            'status' => 'success',
            'message' => 'Вы успешно вышли из системы',
        ];
    }

    return [
        'status' => 'error',
        'message' => 'Не удалось выйти из системы',
    ];
}


public function actionZapis()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    // Получение токена из заголовка Authorization
    $token = Yii::$app->request->getHeaders()->get('Authorization');

    if (!$token || strpos($token, 'Bearer ') !== 0) {
        return [
            'status' => 'error',
            'message' => 'Токен отсутствует или некорректный формат.',
        ];
    }

    $token = substr($token, 7);

    // Поиск пользователя по токену
    $user = User::findOne(['token' => $token]);

    if (!$user) {
        return [
            'status' => 'error',
            'message' => 'Пользователь не аутентифицирован.',
        ];
    }

    // Проверка роли
    if ($user->role !== 'admin') {
        return [
            'status' => 'error',
            'message' => 'У вас нет прав для просмотра записей.',
        ];
    }

    // Получение всех записей
    $zapis = Zapis::find()->asArray()->all();

    if (!empty($zapis)) {
        return [
            'status' => 'success',
            'data' => $zapis,
        ];
    }

    return [
        'status' => 'error',
        'message' => 'Записи не найдены.',
    ];
}



public function actionUpdateZapis($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    // Проверка, является ли пользователь администратором
    $user = Yii::$app->user->identity;
    if (!$user || $user->role !== 'admin') {
        return [
            'status' => 'error',
            'message' => 'У вас нет прав для изменения расписания.',
        ];
    }

    // Получаем id мастера из запроса
    $id_master = Yii::$app->request->post('id_master');
    if (!$id_master || !is_numeric($id_master)) {
        return [
            'status' => 'error',
            'message' => 'Некорректный ID мастера.',
        ];
    }

    // Проверка существования мастера с указанным ID
    $master = User::findOne(['id' => $id_master, 'role' => 'master']);
    if (!$master) {
        return [
            'status' => 'error',
            'message' => 'Такого ID нет в системе.',
        ];
    }

    // Проверка существования записи
    $zapis = Zapis::findOne($id);
    if (!$zapis) {
        return [
            'status' => 'error',
            'message' => 'Запись не найдена.',
        ];
    }

    // Обновление данных записи
    $zapis->date = Yii::$app->request->post('date');
    $zapis->time = Yii::$app->request->post('time');
    $zapis->id_master = $id_master;

    // Сохранение изменений с обработкой ошибок
    try {
        if ($zapis->validate() && $zapis->save()) {
            return [
                'status' => 'success',
                'message' => 'Запись успешно обновлена.',
            ];
        }
    } catch (\yii\db\IntegrityException $e) {
        Yii::$app->response->statusCode = 400;
        return [
            'status' => 'error',
            'message' => 'Ошибка целостности данных. Проверьте указанный id мастера.',
            'details' => $e->getMessage(),
        ];
    }

    return [
        'status' => 'error',
        'message' => 'Не удалось обновить запись.',
        'errors' => $zapis->errors,
    ];
}

public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = $this->getUserByToken();
        if (!$user) {
            return $this->createErrorResponse('Пользователь не найден или токен недействителен.');
        }

        $data = Yii::$app->request->post();
        if (empty($data)) {
            return $this->createErrorResponse('Данные для обновления отсутствуют.');
        }

        if ((int)$data['user_id'] !== $user->id) {
            return $this->createErrorResponse('Вы можете обновлять только свои данные.');
        }

        $user->login = $data['login'] ?? $user->login;
        $user->full_name = $data['full_name'] ?? $user->full_name;
        $user->email = $data['email'] ?? $user->email;
        $user->phone = $data['phone'] ?? $user->phone;

        if (User::find()->where(['email' => $data['email']])->andWhere(['!=', 'id', $user->id])->exists()) {
            return $this->createErrorResponse('Этот email уже используется.');
        }

        if (User::find()->where(['phone' => $data['phone']])->andWhere(['!=', 'id', $user->id])->exists()) {
            return $this->createErrorResponse('Этот телефон уже используется.');
        }

        if ($user->validate() && $user->save()) {
            return [
                'status' => 'success',
                'message' => 'Данные обновлены',
            ];
        }

        Yii::error('Ошибка обновления профиля: ' . json_encode($user->errors), 'user');
        return $this->createErrorResponse('Ошибка валидации', $user->errors, 422);
    }

    // Метод для получения пользователя по токену
    protected function getUserByToken()
    {
        $token = Yii::$app->request->headers->get('Authorization');
        if (!$token) {
            return null;
        }

        // Предполагается, что токен хранится в поле token
        return User::findOne(['token' => str_replace('Bearer ', '', $token)]);
    }

    // Метод для формирования ответа с ошибкой
    protected function createErrorResponse($message, $errors = [], $statusCode = 400)
    {
        Yii::$app->response->statusCode = $statusCode;
        return [
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ];
    }

}
