<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\RO */

$this->title = 'Create Ro';
$this->params['breadcrumbs'][] = ['label' => 'Ros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ro-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
