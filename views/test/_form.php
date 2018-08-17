<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\RO */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ro-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'orderno')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'batchno')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
