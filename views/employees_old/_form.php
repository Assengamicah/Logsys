
<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Add/Update Employee Form.</b></font><hr />
        </h4>
       
    <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,
	]); ?>
                   
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'empcode') ?>
			<?= $form->field($model, 'mname') ?>
			<?= $form->field($model, 'gender')->widget(Select2::classname(), [
            'data' => ['Male'=>'Male','Female'=>'Female'],
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			<?= $form->field($model, 'titleid')->widget(Select2::classname(), [
            'data' => $model->getTittle(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			<?= $form->field($model, 'zid')->widget(Select2::classname(), [
            'data' => $model->getZone(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			<?= $form->field($model, 'uname') ?>
			<?= $form->field($model, 'fdate')->widget(DatePicker::classname(),[ 'pluginOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy','todayHighlight' => true ]]); ?>
			
       </div>
	   
	    <div class="col-md-6">
            <?= $form->field($model, 'fname') ?>
			<?= $form->field($model, 'sname') ?>
			<?= $form->field($model, 'email') ?>
			<?= $form->field($model, 'telno') ?>
			<?= $form->field($model, 'reportsto')->widget(Select2::classname(), [
            'data' => $model->getTittle(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			<?= $form->field($model, 'role')->widget(Select2::classname(), [
            'data' => $model->getRole(),
			'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			<?= $form->field($model, 'tdate')->widget(DatePicker::classname(),[ 'pluginOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy','todayHighlight' => true ]]); ?>

       </div>
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnRegister" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Register Staff </span> <i class="icon-arrow-right13"></i>
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
	



