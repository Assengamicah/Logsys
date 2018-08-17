<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
	<h4 class="panel-title text-bold-500">
            <b>Edit Order Items.</b></font>
        </h4>
	<?php echo $tbDet; ?><hr /><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>
					 <div class="form-group text-center">
                <button type="submit" name ="btnGeneral" class="btn grayish"><span class="text-regular small">Update Receive Order </span> 
                </button>
            </div> 

					<?php } ?><hr />
		
		
		<table class='table table-bordered'><thead><tr bgcolor='grey'>
		<th>Group Name</th><th>Item Name</th><th>Charge Calc.</th><th>Action</th></tr></thead>
		<tr><td width=23%>
		<?= $form->field($model, 'gid')->widget(Select2::classname(), [
            'data' => $model->getGitems(),'options' => ['placeholder' => 'Select'],
            ])->label(false) ?>
		</td>
		
		<td width=52%><?= $form->field($model, 'iid')->widget(DepDrop::classname(), ['type'=>DepDrop::TYPE_SELECT2,
                'data'=>$model->getItems(),'options' => ['placeholder' => 'Select','multiple' => true],
                'pluginOptions'=>[
                'depends'=>['orderitemsb-gid'],
                'url'=>Url::to(['/operation/getsitems'])
               ]
             ])->label(false) ?></td>
			<td width=12%><?= $form->field($model, 'pcalc')->dropDownList(['LATER'=>'LATER'])->label(false) ?></td>
			<td><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add </span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile('@web/js/enable-disable.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
	

