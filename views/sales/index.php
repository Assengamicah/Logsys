<?php

use yii\helpers\Html;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */


$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">

    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title text-bold-500">
            Sales Module
        </div>
		</div>
        <hr class="no-margin-bottom">
		 <?php  $form = ActiveForm::begin([
            'id' => 'login2-form-horizontal', 
            'type' => ActiveForm::TYPE_HORIZONTAL,
			'enableClientValidation'=>false,
			'enableAjaxValidation'=>false,
            
        ]); 
      ?>     
        <table class="table table-gray">
            <thead>
            <tr>
                
				<th width="56%">&nbsp;</th>
				<th align="right">
                   
	 
             <?= $form->field($model, 'barcode',['addon' => ['append' => ['content' => Html::submitButton('Trace', ['class'=>'btn primary','name'=>'btnSNumber']), 'asButton' => true]]])->textInput(['autofocus' => true,'placeholder'=>'Please Provide Product Barcode To Trace'])->label(false); ?>
						 
                </th>
                
            </tr></thead>
           
           
        </table>
		 <?php ActiveForm::end(); ?>
		 <?php echo $tbTrace; ?>
    </div>
</div>