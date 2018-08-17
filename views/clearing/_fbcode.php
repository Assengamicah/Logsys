<?php

use yii\helpers\Html;
use yii\helpers\Url;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;


//use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Product Barcode Generator Form</b></font></h4>
        
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
       <div class="col-md-10">
	        
			 <?= $form->field($model, 'refid') ?>			 
		</div>				                        
	
    </div>
	   <div class="form-group text-center">
	     <button type="submit" name ="btnContact" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Generate </span> 
                </button>
						 
		</div>                    
			

			
   
    <?php ActiveForm::end(); ?>
	 </div>

	</div>
	

