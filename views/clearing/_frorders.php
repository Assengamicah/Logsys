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
            <b>Receive Orded Goods Form</b></font></h4>
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
	   <?= $form->field($model, 'prodid')->widget(Select2::classname(), [
            'data' => $model->getProducts(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
       <?= $form->field($model, 'quantity')->widget(DepDrop::classname(), ['type'=>DepDrop::TYPE_SELECT2,
                'data'=>$model->getQuantities(),
                'options'=>['placeholder'=>'Select'],
                'select2Options'=>['pluginOptions'=>['allowClear'=>true,]],
                'pluginOptions'=>[
                'depends'=>['rorders-prodid'],
                'url'=>Url::to(['/operation/getquantities'])
               ]
             ]); ?>
        <?= $form->field($model, 'dweight'); ?>
		<?= $form->field($model, 'dwunit')->dropDownList($model->getUnit(),['prompt'=>'Select']); ?>
		<?= $form->field($model, 'locid')->dropDownList($model->getLocations(),['prompt'=>'Select']); ?>
						 
		</div>				                        
	 
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnContact" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Save </span> 
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>

	</div>
	

