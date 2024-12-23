<?php

namespace app\models;

use Yii;
use app\models\Services;
use yii\base\ErrorException;

/**
 * This is the model class for table "Zapis".
 *
 * @property int $id
 * @property int $id_client
 * @property int $id_master
 * @property int $id_services
 * @property string $date
 * @property string $time
 *
 * @property User $client
 * @property User $master
 * @property Services $services
 */
class Zapis extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'Zapis';
    }

    public function rules()
    {
        return [
            [['id_services'], 'integer'],
            [['id_client', 'id_master', 'id_services', 'date', 'time'], 'required'],
            [['id_client', 'id_master', 'id_services'], 'integer'],
            [['date', 'time'], 'safe'],
            // Исключаем уникальность при обновлении записи
            [['id_services'], 'unique', 'targetClass' => self::class, 'message' => 'Запись с таким названием уже существует.', 'on' => 'insert'],
            [['date', 'time'], 'validateMasterAvailability'], // для проверки доступности мастера
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_client' => 'Client ID',
            'id_master' => 'Master ID',
            'id_services' => 'Service ID',
            'date' => 'Date',
            'time' => 'Time',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->date) {
                // Преобразование формата даты из DD.MM.YYYY в YYYY-MM-DD
                $date = \DateTime::createFromFormat('d.m.Y', $this->date);
                if ($date === false) {
                    throw new ErrorException('Неверный формат даты.');
                }
                $this->date = $date->format('Y-m-d');
            }
            return true;
        }
        return false;
    }

    // Метод валидации для проверки доступности мастера
    public function validateMasterAvailability($attribute, $params)
    {
        $existingRecord = Zapis::find()
            ->where(['id_master' => $this->id_master, 'date' => $this->date, 'time' => $this->time])
            ->exists();

        if ($existingRecord) {
            $this->addError($attribute, 'Мастер уже занят в это время.');
        }
    }

    public function getClient()
    {
        return $this->hasOne(User::class, ['id' => 'id_client']);
    }

    public function getMaster()
    {
        return $this->hasOne(User::class, ['id' => 'id_master']);
    }

    public function getServices()
    {
        return $this->hasOne(Services::class, ['id' => 'id_services']);
    }

    public function beforeValidate()
    {
        if (!$this->isNewRecord) {
            // Для обновлений, исключаем проверку на уникальность
            $this->clearUniqueValidators('id_services');
        }

        return parent::beforeValidate();
    }

    private function clearUniqueValidators($attribute)
    {
        foreach ($this->validators as $key => $validator) {
            if ($validator instanceof \yii\validators\UniqueValidator && $validator->attributes === [$attribute]) {
                unset($this->validators[$key]);
            }
        }
    }
}
