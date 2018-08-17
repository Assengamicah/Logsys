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
            Product Sale Panel<hr />
			<?php echo $tbSale; ?>
			<hr />
        </h4>
        
       <?php if (Yii::$app->session->hasFlash('ssuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('ssuccess'); ?>
        </div>
	 <?php } ?>	
    <?php  $form = ActiveForm::begin([
            'id' => 'login2-form-horizontal', 
            'type' => ActiveForm::TYPE_HORIZONTAL,
			'enableClientValidation'=>false,
			'enableAjaxValidation'=>false,
            'formConfig' => ['labelSpan' => 4,'deviceSize' => ActiveForm::SIZE_SMALL]
        ]); 
      ?>     
	 
             <?= $form->field($model, 'barcode',['addon' => ['append' => ['content' => Html::submitButton('OK', ['class'=>'btn primary','name'=>'btnSNumber']), 'asButton' => true]]])->textInput(['autofocus' => true]); ?>
						 
						 
				
			

    <hr />  
   
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
	

