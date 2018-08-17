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
            <b>Order Receiving Step1 </b></font></h4>
        
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
       <div class="col-md-7">
                          <?= $form->field($model, 'cid')->widget(Select2::classname(), [
            'data' => $model->getClients(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			<?= $form->field($model, 'sas') ?>
						 <?= $form->field($model, 'orderdate')->widget(DatePicker::classname(),['type' => DatePicker::TYPE_COMPONENT_APPEND, 'pluginOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy','todayHighlight' => true ]]); ?>
		</div>				                        
	 
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnContact" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Proceed >> </span> 
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>

	</div>
	

