<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\Zapis;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use app\models\User;

class ZapisController extends Controller
{
    public $modelClass = 'app\models\Zapis';

    

    /**
     * Проверка прав администратора.
     */
    // private function checkAdminAccess()
    // {
    //     if (!Yii::$app->user->identity || !Yii::$app->user->identity->isAdmin()) {
    //         throw new ForbiddenHttpException('Доступ запрещен. Только администратор может выполнять это действие.');
    //     }
    // }

    /**
     * Получение записи по ID.
     */
    private function findZapisById($id)
    {
        if (!$id) {
            throw new BadRequestHttpException("ID записи не указан.");
        }

        $zapis = Zapis::findOne($id);

        if (!$zapis) {
            throw new NotFoundHttpException("Запись с ID $id не найдена.");
        }

        return $zapis;
    }

    /**
     * Создание записи.
     */
public function actionCreate()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    // Получение токена из заголовка Authorization
    $authHeader = Yii::$app->request->headers->get('Authorization');
    $token = null;

    if ($authHeader && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
        $token = $matches[1]; // Извлекаем токен из заголовка
    }

    if (!$token) {
        return [
            'status' => 'error',
            'message' => 'Токен авторизации не предоставлен.',
        ];
    }

    // Поиск пользователя по токену
    $user = User::findOne(['token' => $token]);

    if (!$user) {
        return [
            'status' => 'error',
            'message' => 'Пользователь не аутентифицирован.',
        ];
    }

    // Получение данных из запроса
    $data = Yii::$app->request->post();
    $id_master = $data['id_master'] ?? null;

    // Проверка, указан ли мастер и его валидность
    if (!$id_master || !is_numeric($id_master)) {
        return [
            'status' => 'error',
            'message' => 'Некорректный ID мастера.',
        ];
    }

    // Проверка существования мастера
    $master = User::findOne(['id' => $id_master, 'role' => 'master']);
    if (!$master) {
        return [
            'status' => 'error',
            'message' => 'Мастер с указанным ID не найден.',
        ];
    }

    // Проверка на дублирование записи
    $existingZapis = Zapis::find()
        ->where([
            'date' => $data['date'],
            'time' => $data['time'],
            'id_master' => $id_master
        ])
        ->one();

    if ($existingZapis) {
        return [
            'status' => 'error',
            'code' => 422,
            'message' => 'Запись на эту дату и время уже существует.',
        ];
    }

    // Создание новой записи
    $zapis = new Zapis();
    $zapis->date = $data['date'];
    $zapis->time = $data['time'];
    $zapis->id_master = $id_master;
    $zapis->id_client = $user->id; // Клиент берётся из авторизованного пользователя
    $zapis->id_services = $data['id_services'] ?? null;

    // Сохранение данных с обработкой ошибок
    try {
        if ($zapis->validate() && $zapis->save()) {
            return [
                'status' => 'success',
                'message' => 'Запись успешно создана.',
                'data' => $zapis,
            ];
        }
    } catch (\yii\db\Exception $e) {
        Yii::$app->response->statusCode = 400;
        return [
            'status' => 'error',
            'message' => 'Ошибка при сохранении записи.',
            'details' => $e->getMessage(),
        ];
    }

    return [
        'status' => 'error',
        'code' => 422,
        'message' => 'Ошибка при создании записи.',
        'errors' => $zapis->errors,
    ];
}




    /**
     * Обновление записи (по ролям).
     */
    public function actionUpdate()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $token = Yii::$app->request->getHeaders()->get('Authorization');
    if (!$token || strpos($token, 'Bearer ') !== 0) {
        return [
            'status' => 'error',
            'message' => 'Необходимо войти в систему.',
        ];
    }

    // Извлекаем токен без "Bearer "
    $token = substr($token, 7);

    $user = User::findOne(['token' => $token]);
    if (!$user) {
        return [
            'status' => 'error',
            'message' => 'Пользователь не найден или токен недействителен.',
        ];
    }

    $id = Yii::$app->request->post('id');
    $zapis = Zapis::findOne($id);

    if (!$zapis) {
        return [
            'status' => 'error',
            'message' => 'Запись не найдена.',
        ];
    }

    if ($user->role === 'admin') {
        // Администратор может изменять все поля
        $zapis->attributes = Yii::$app->request->post();
    } elseif ($user->role === 'client' && $user->id === $zapis->id_client) {
        // Клиент может изменять только свои записи (дата, время, услуга)
        $zapis->date = Yii::$app->request->post('date', $zapis->date);
        $zapis->time = Yii::$app->request->post('time', $zapis->time);
        $zapis->id_services = Yii::$app->request->post('id_services', $zapis->id_services);
    } else {
        return [
            'status' => 'error',
            'message' => 'У вас нет прав для изменения этой записи.',
        ];
    }

    // Проверка на то, чтобы не назначать клиента или администратора как мастера
    $master = User::findOne($zapis->id_master);
    if ($master && ($master->role === 'client' || $master->role === 'admin')) {
        return [
            'status' => 'error',
            'message' => 'Нельзя назначить клиента или администратора в качестве мастера.',
        ];
    }

    // Игнорируем проверку уникальности при обновлении
    if ($zapis->validate(null, false) && $zapis->save(false)) {
        return [
            'status' => 'success',
            'message' => 'Запись успешно обновлена.',
            'data' => $zapis,
        ];
    }

    return [
        'status' => 'error',
        'message' => 'Ошибка при обновлении записи.',
        'errors' => $zapis->errors,
    ];
}




    /**
     * Удаление записи
     */
    public function actionDelete()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    // Получение токена из заголовка Authorization
    $authHeader = Yii::$app->request->getHeaders()->get('Authorization');
    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
        return [
            'status' => 'error',
            'message' => 'Необходимо войти в систему.',
        ];
    }

    $token = str_replace('Bearer ', '', $authHeader);
    $user = User::findOne(['token' => $token]);

    if (!$user) {
        return [
            'status' => 'error',
            'message' => 'Неверный токен или пользователь не найден.',
        ];
    }

    // Получение ID записи из запроса
    $id = Yii::$app->request->get('id');
    if (empty($id)) {
        return [
            'status' => 'error',
            'message' => 'ID записи не указан.',
        ];
    }

    // Поиск записи
    $zapis = Zapis::findOne($id);
    if (!$zapis) {
        return [
            'status' => 'error',
            'message' => "Запись с ID $id не найдена.",
        ];
    }

    // Удаление записи
    if ($zapis->delete()) {
        return [
            'status' => 'success',
            'message' => "Запись с ID $id успешно удалена.",
        ];
    }

    // Обработка неудачного удаления
    return [
        'status' => 'error',
        'message' => "Не удалось удалить запись с ID $id.",
    ];
}


    public function actionAdminzapisi()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    // Проверка, что текущий пользователь - администратор
    if (!Yii::$app->user->identity->isAdmin()) {
        return [
            'status' => 'error',
            'message' => 'Недостаточно прав для доступа к этому ресурсу.',
        ];
    }

    // Получаем все записи
    $zapis = Zapis::find()->all();

    // Если записи найдены
    if ($zapis) {
        return [
            'status' => 'success',
            'data' => $zapis,
        ];
    }

    // Если записей нет
    return [
        'status' => 'error',
        'message' => 'Записи не найдены.',
    ];
}


}
// var_dump($id); die;
