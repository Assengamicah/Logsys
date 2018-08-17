<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
	<h4 class="panel-title text-bold-500">
            <b>Stock Movement.</b></font>
        </h4>
	<hr /><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>
					 <div class="form-group text-center">
                <button type="submit" name ="btnGeneral" class="btn grayish"><span class="text-regular small">Move </span> 
                </button>
            </div> 

					<?php } ?><hr />
		
		
		<table class='table table-bordered'><thead><tr bgcolor='grey'>
		<th width=38%>ORDER</th><th width=30%>PRODUCT AND ITS CURRENT LOCATION</th><th width=22%>NEW LOCATION</th><th>ACTION</th></tr></thead>
		<tr>
		<td><?= $form->field($model, 'orderno')->widget(Select2::classname(), [
            'data' => $model->getOrders(),'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
			
		<td><?= $form->field($model, 'prodid')->widget(DepDrop::classname(), ['type'=>DepDrop::TYPE_SELECT2,
                'data'=>$model->getProducts(),'options'=>['placeholder'=>'Select'],'select2Options'=>['pluginOptions'=>['allowClear'=>true,]],
                'pluginOptions'=>[
                'depends'=>['stock-orderno'],
                'url'=>Url::to(['/operation/getploc'])
               ]
             ])->label(false) ?></td>
		<td><?= $form->field($model, 'locid')->dropDownList($model->getLocations(),['prompt'=>'Select'])->label(false) ?></td>
			<td><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add </span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
	

