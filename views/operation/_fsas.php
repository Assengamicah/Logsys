<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;

?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Shipping Name Registration Form.</b></font><hr />
        </h4>
		<?php if (Yii::$app->session->hasFlash('ossuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('ossuccess'); ?>
        </div>
		<?php } ?>	
		
		<?php if (Yii::$app->session->hasFlash('rsuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('rsuccess'); ?>
        </div>
		<?php } ?>
		<?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,
		]); ?>
	   
	
	<div class="row">
        <div class="col-md-6">
			<?= $form->field($model, 'cid')->widget(Select2::classname(), [
            'data' => $model->getClients(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
       </div>
	   
	   <div class="col-md-6">
            <?= $form->field($model, 'sas') ?>
       </div>
    </div>
	                     
	<div class="form-group text-center">
		<button type="submit" name ="btnRegister" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Save </span>
		</button>
	</div> <br />

			<?php echo $tbC; ?>
   
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
	

