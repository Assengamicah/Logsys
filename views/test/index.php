<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ros';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ro-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ro', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'itemid',
            'orderno',
            'prodid',
            'batchno',
            //'barcode',
            //'weight',
            //'munit',
            //'dweight',
            //'dwunit',
            //'price',
            //'eddate',
            //'rdate',
            //'rtime',
            //'locid',
            //'cby',
            //'cdate',
            //'rby',
            //'creason',
            //'canby',
            //'candate',
            //'status',
            //'chopped',
            //'chopdate',
            //'rweight',
            //'rwunit',
            //'cweight',
            //'cwunit',
            //'sby',
            //'sdate',
            //'stime',
            //'olocid',
            //'movcode',
            //'movby',
            //'movdate',
            //'movtime',
            //'cicode',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
