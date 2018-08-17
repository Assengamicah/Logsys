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
                <button type="submit" name ="btnGeneral" class="btn green"><span class="text-regular small">Update Received Cargo </span> 
                </button>
            </div> 

					<?php } ?><hr />
		
		
		<table class='table table-bordered'><thead><tr bgcolor='grey'>
		<th>Group Name</th><th>Item Name</th><th># Of CBM/Bundle</th><th>Control Number</th><th>Charge Calc.</th><th>Action</th></tr></thead>
		<tr><td width=22%>
		<?= $form->field($model, 'gid')->widget(Select2::classname(), [
            'data' => $model->getGitems(),'options' => ['placeholder' => 'Select'],
            ])->label(false) ?>
		</td>
		
		<td width=30%><?= $form->field($model, 'iid')->widget(DepDrop::classname(), ['type'=>DepDrop::TYPE_SELECT2,
                'data'=>$model->getItems(),'options' => ['placeholder' => 'Select','multiple' => true],
                'pluginOptions'=>[
                'depends'=>['orderitems-gid'],
                'url'=>Url::to(['/operation/getsitems'])
               ]
             ])->label(false) ?></td>
		    <td><?= $form->field($model, 'cbm')->label(false) ?></td>
			<td><?= $form->field($model, 'cno')->label(false) ?></td>
			<td width=12%><?= $form->field($model, 'pcalc')->dropDownList(['LATER'=>'LATER','NOW'=>'NOW'])->label(false) ?></td>
			<td><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add </span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile('@web/js/enable-disable.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
	

