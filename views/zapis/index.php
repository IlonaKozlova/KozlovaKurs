<?php

use app\models\Zapis;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ZapisSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Zapis';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zapis-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Zapis', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'id_client',
            'id_master',
            'id_services',
            'phone',
            //'date',
            //'time',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Zapis $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
