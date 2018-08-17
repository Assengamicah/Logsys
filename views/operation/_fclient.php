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
            <b>New Client Registration Form.</b></font><hr />
        </h4>
       <?php if (Yii::$app->session->hasFlash('ossuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('ossuccess'); ?>
        </div>
	 <?php } ?>	
    <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,
	]); ?>
                   
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name') ?>
       </div>
	   
	    <div class="col-md-6">
			<?= $form->field($model, 'ctype')->widget(Select2::classname(), [
            'data' => ['INDIVIDUAL'=>'INDIVIDUAL','COMPANY'=>'COMPANY'],
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
       </div>
    </div>
	
	<div class="row">
        <div class="col-md-2">
			<?= $form->field($model, 'pcode')->widget(Select2::classname(), [
            'data' => $model->getPcode(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,'width' => '140px'],])  ?>	
       </div>
	   
	   <div class="col-md-4">
            <?= $form->field($model, 'phone')->textInput(['style'=>'width:400px'])->widget(\yii\widgets\MaskedInput::className(),['mask'=>'999999999']) ?>
       </div>
	   
	   <div class="col-md-2">
			<?= $form->field($model, 'pcode2')->widget(Select2::classname(), [
            'data' => $model->getPcode(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,'width' => '140px',]])  ?>	
       </div>
	   
	   <div class="col-md-4">
            <?= $form->field($model, 'phone2')->textInput(['style'=>'width:400px'])->widget(\yii\widgets\MaskedInput::className(),['mask'=>'999999999']) ?>
       </div>
    </div>
	
	<div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'address') ?>
			<?= $form->field($model, 'email') ?>
			
       </div>
	   
	    <div class="col-md-6">
            <?= $form->field($model, 'paddress') ?>

       </div>
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnRegister" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Save </span>
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
	

