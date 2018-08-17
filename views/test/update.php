<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RO */

$this->title = 'Update Ro: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Ros', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ro-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
