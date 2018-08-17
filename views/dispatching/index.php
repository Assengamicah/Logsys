<?php

/* @var $this yii\web\View */
use yii\grid\GridView;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = 'Client Profiles';

?>



<div class="panel">
    <div class="panel-heading">
	 <?php if (Yii::$app->session->hasFlash('asuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('asuccess'); ?>
        </div>
	 <?php } ?>
        <h4 class="panel-title text-bold-500">
		 <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL,
		'enableClientValidation'=>false,'enableAjaxValidation'=>false,'validateOnSubmit' => false,
		]); ?>
		<table>
		<tr><td width='70%'><b>Items Delivery Module</b></td><td align=right><?= $form->field($model, 'rlno',['addon' => ['append' => [
                    'content' => Html::submitButton('Search', ['class'=>'btn primary','name'=>'btnSName']), 'asButton' => true]]])->textInput(['placeholder' => "Release Number",'style'=>'width:300px'])->label(false); ?></td></tr></table>
         <?php ActiveForm::end(); ?>
            <hr />
        </h4>
        
    
    <div class="table-responsive">

        <?php
					  	  
					   echo  $tbH.'<br />'.$tbD;
					  
                    ?>

    </div>
	</div>
</div>




