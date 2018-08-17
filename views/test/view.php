<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\RO */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ro-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'itemid',
            'orderno',
            'prodid',
            'batchno',
            'barcode',
            'weight',
            'munit',
            'dweight',
            'dwunit',
            'price',
            'eddate',
            'rdate',
            'rtime',
            'locid',
            'cby',
            'cdate',
            'rby',
            'creason',
            'canby',
            'candate',
            'status',
            'chopped',
            'chopdate',
            'rweight',
            'rwunit',
            'cweight',
            'cwunit',
            'sby',
            'sdate',
            'stime',
            'olocid',
            'movcode',
            'movby',
            'movdate',
            'movtime',
            'cicode',
        ],
    ]) ?>

</div>
