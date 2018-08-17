<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Employees */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="employees-form">

    <?php $form = ActiveForm::begin(); ?>
	
	<table style = "border-collapse: separate; border-spacing: 20px;">

    <tr><td><?= $form->field($model, 'empcode')->textInput(['style'=>'width:500px']) ?></td>

    <td><?= $form->field($model, 'fname')->textInput(['style'=>'width:500px']) ?></td></tr>

    <tr><td><?= $form->field($model, 'mname')->textInput(['style'=>'width:500px']) ?></td>

    <td><?= $form->field($model, 'sname')->textInput(['style'=>'width:500px']) ?></td></tr>

    <tr><td><?= $form->field($model, 'gender')->dropDownList(['Male' => 'Male', 'Female' => 'Female'], ['prompt' => 'Select','style'=>'width:500px']) ?></td>

    <td><?= $form->field($model, 'email')->textInput(['style'=>'width:500px']) ?></td></tr>

    <tr><td><?= $form->field($model, 'titleid')->dropDownList($model->getTittle(),['prompt' => 'Select', 'style'=>'width:500px']) ?></td>

    <td><?= $form->field($model, 'telno')->textInput(['style'=>'width:500px']) ?></td></tr>

    <tr><td><?= $form->field($model, 'zid')->dropDownList($model->getZone(),['prompt' => 'Select', 'style'=>'width:500px']) ?></td>

    <td><?= $form->field($model, 'reportsto')->dropDownList($model->getTittle(),['prompt' => 'Select', 'style'=>'width:500px']) ?></td></td></tr>
	
	</table>

    <div class="form-group">
       <center> <?= Html::submitButton('Update Staff', ['class' => 'btn btn-success']) ?></center>
    </div>

    <?php ActiveForm::end(); ?>

</div>
