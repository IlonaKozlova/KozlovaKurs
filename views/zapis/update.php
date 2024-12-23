<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Zapis $model */

$this->title = 'Update Zapis: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Zapis', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="zapis-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
