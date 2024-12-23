<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ZapisSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="zapis-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_client') ?>

    <?= $form->field($model, 'id_master') ?>

    <?= $form->field($model, 'id_services') ?>

    <?= $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'date') ?>

    <?php // echo $form->field($model, 'time') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
