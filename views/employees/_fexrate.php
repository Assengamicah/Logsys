<?php

use yii\helpers\Html;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;


?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            Current Exchange Rate: <font color="#0000CC"><?php echo number_format(Yii::$app->user->identity->rate,2); ?></font><hr />
        </h4>
        
       <?php if (Yii::$app->session->hasFlash('isuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('isuccess'); ?>
        </div>
	 <?php } ?>	
    <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>
	        <div class="row">
			<div class="col-md-7">
             <?= $form->field($model, 'erate',['addon' => ['append' => ['content' => Html::submitButton('Add New Rate', ['class'=>'btn success','name'=>'btnSNumber']), 'asButton' => true]]])->textInput(['placeholder' => "New Exchange Rate"])->label(false); ?>
						 
		</div>
       </div>		
				
			

    <hr />
      
   
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
	

