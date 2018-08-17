<?php

use yii\helpers\Html;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            Raw Material Registration/Updation Form<hr />
        </h4>
        
       <?php if (Yii::$app->session->hasFlash('isuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('isuccess'); ?>
        </div>
	 <?php } ?>	
    <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>
	 
             <?= $form->field($model, 'name',['addon' => ['append' => ['content' => Html::submitButton('Save Item', ['class'=>'btn primary','name'=>'btnSNumber']), 'asButton' => true]]])->textInput(['placeholder' => "Raw Item Name"])->label(false); ?>
						 
						 
				
			

    <hr />
      <?php echo $tbItem; ?> <hr />  
   
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
	

