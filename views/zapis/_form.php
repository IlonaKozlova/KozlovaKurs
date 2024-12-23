<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Zapis $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="zapis-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_client')->textInput() ?>

    <?= $form->field($model, 'id_master')->textInput() ?>

    <?= $form->field($model, 'id_services')->textInput() ?>

    <?= $form->field($model, 'phone')->textInput() ?>

    <?= $form->field($model, 'date')->textInput() ?>

    <?= $form->field($model, 'time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
