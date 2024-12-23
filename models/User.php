<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * Указываем имя таблицы.
     */
    public static function tableName()
    {
        return 'User'; // Имя таблицы в БД с большой буквы
    }

    /**
     * Правила валидации.
     */
    public function rules()
    {
        return [
            [['login', 'full_name', 'email', 'phone', 'password'], 'required'],
            [['role'], 'in', 'range' => ['admin', 'master', 'client']],
            [['role'], 'default', 'value' => 'client'],
            [['login', 'email'], 'unique'],
            [['token'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['phone'], 'match', 'pattern' => '/^\+?[0-9]{10,15}$/', 'message' => 'Неверный формат телефона'],
            [['login'], 'string', 'max' => 50],
            [['full_name', 'email'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 15],
            [['password'], 'string', 'max' => 255],
        ];
    }

    /**
     * Подписи атрибутов.
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'full_name' => 'Full Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'password' => 'Password',
            'role' => 'Role',
            'token' => 'Token',
        ];
    }

    /**
     * Реализуем IdentityInterface

     * Находит идентичность (пользователя) по ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Находит идентичность по токену.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token' => $token]);
    }

    /**
     * Получение ID пользователя.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Получение токена.
     */
    public function getAuthKey()
    {
        return $this->token;
    }

    /**
     * Проверка, совпадает ли ключ (например, для аутентификации).
     */
    public function validateAuthKey($authKey)
    {
        return $this->token === $authKey;
    }

    /**
     * Получение пользователя по токену.
     */
    public static function getByToken($token)
    {
        return self::findOne(['token' => $token]);
    }

    /**
     * Обработчик перед сохранением.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Хэшируем пароль, если он был изменён
            if ($this->isAttributeChanged('password')) {
                $this->password = Yii::$app->security->generatePasswordHash($this->password);
            }
            // Генерируем токен, если это новая запись или токен пустой
            if ($this->isNewRecord || empty($this->token)) {
                $this->token = Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }

    /**
     * Проверка, является ли пользователь администратором.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Связь с записями клиента.
     */
    public function getClientZapis()
    {
        return $this->hasMany(Zapis::class, ['id_client' => 'id']);
    }

    /**
     * Связь с записями мастера.
     */
    public function getMasterZapis()
    {
        return $this->hasMany(Zapis::class, ['id_master' => 'id']);
    }

    /**
     * Найти пользователя по логину.
     *
     * @param string $login
     * @return static|null
     */
    public static function findByLogin($login)
    {
        return static::findOne(['login' => $login]);
    }

    /**
     * Проверка пароля.
     *
     * @param string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }
}
