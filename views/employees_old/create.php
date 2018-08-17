<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Employees */

$this->title = 'Staff Registration Form';
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


	

    <?= $this->render('_form', ['model' => $model,]) ?>


