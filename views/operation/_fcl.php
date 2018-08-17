<?php

use yii\helpers\Html;
use yii\helpers\Url;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;

//use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Container Loading Step 1. </b></font></h4>
        
      <hr />		
    <?php  $form = ActiveForm::begin([
            'id' => 'login2-form-horizontal', 
            'type' => ActiveForm::TYPE_HORIZONTAL,
			'enableClientValidation'=>false,
			'enableAjaxValidation'=>false,
            'formConfig' => ['labelSpan' => 3,'deviceSize' => ActiveForm::SIZE_SMALL]
        ]); 
      ?>                  
	<div class="row">
       <div class="col-md-11">
                          <?= $form->field($model, 'slid')->widget(Select2::classname(), [
            'data' => $model->getSLine(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			             <?= $form->field($model, 'containerno') ?>
						 <?= $form->field($model, 'cntsize')->widget(Select2::classname(), [
                        'data' => ['20 FT'=>'20 FT','40 FT'=>'40 FT'],'options' => ['placeholder' => 'Select'],]) ?>
						  <?= $form->field($model, 'expsdate')->widget(DatePicker::classname(),['type' => DatePicker::TYPE_COMPONENT_APPEND, 'pluginOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy','todayHighlight' => true ]]); ?>
						 <?= $form->field($model, 'expardate')->widget(DatePicker::classname(),['type' => DatePicker::TYPE_COMPONENT_APPEND, 'pluginOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy','todayHighlight' => true ]]); ?>
		</div>				                        
	 
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnContact" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Next >> </span> 
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>

	</div>
	

