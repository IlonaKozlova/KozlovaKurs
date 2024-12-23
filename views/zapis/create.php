<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Zapis $model */

$this->title = 'Create Zapis';
$this->params['breadcrumbs'][] = ['label' => 'Zapis', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zapis-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
