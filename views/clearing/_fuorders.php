<?php

use yii\helpers\Html;
use yii\helpers\Url;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;


//use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Update Received Orded Goods Form</b></font></h4>
        <?php echo $tbData; ?>
		<?php if (Yii::$app->session->hasFlash('rsuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('rsuccess'); ?>
        </div>
	 <?php } ?>	  
		<hr /><br />
      		
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
	   <?= $form->field($model, 'prodid')->textInput(['readonly'=>true,'maxlength' => true])  ?>
       <?= $form->field($model, 'quantity')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'weight'); ?>
		<?= $form->field($model, 'munit')->dropDownList($model->getUnit(),['prompt'=>'Select']); ?>
		
						 
		</div>				                        
	 
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnContact" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Update </span> 
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>

	</div>
	

