<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;

//use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            Shipping Item Group Registration/Updation Form<hr />
        </h4>
      
    <?php $form = ActiveForm::begin(['layout'=>'horizontal','enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>

	                 <?= $form->field($model, 'name') ?>
					 <?= $form->field($model, 'cper')->widget(Select2::classname(), [
            'data' => ['CBM'=>'CBM','BUNDLE'=>'BUNDLE','BELLOW'=>'BELLOW'],
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			 <?= $form->field($model, 'rate') ?>		
           <div class="form-group text-center">
                <button type="submit" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Save </span> 
                </button>
            </div>
   
    <?php ActiveForm::end(); ?>
	 <hr />
	  <?php if (Yii::$app->session->hasFlash('igsuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('igsuccess'); ?>
        </div>
	 <?php } ?>	
	  <div class="table-responsive">

        <?php echo $tbG; ?>
    </div>
	 </div>
	 
	</div>
	

