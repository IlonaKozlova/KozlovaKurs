<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Services".
 *
 * @property int $id
 * @property string $name_services
 * @property string $description
 * @property float $price
 *
 * @property Zapis[] $zapis
 */
class Services extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name_services', 'description', 'price'], 'required'],
            [['description'], 'string'],
            [['price'], 'number'],
            [['name_services'], 'string', 'max' => 100],

            [['name_services'], 'unique', 'message' => 'Услуга с таким названием уже существует.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_services' => 'Name Services',
            'description' => 'Description',
            'price' => 'Price',
        ];
    }

    /**
     * Gets query for [[Zapis]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getZapis()
    {
        return $this->hasMany(Zapis::class, ['id_services' => 'id']);
    }
}
