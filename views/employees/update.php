<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Employees */

$this->title = 'Update Staff';
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->empid, 'url' => ['view', 'id' => $model->empid]];
$this->params['breadcrumbs'][] = 'Update Staff';
?>
<div class="employees-update">

    <?= $this->render('upstaff_form', [
        'model' => $model,
    ]) ?>

</div>
