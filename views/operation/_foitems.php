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
            <b>Add Order Items.</b></font>
        </h4>
	<?php echo $tbDet; ?><hr /><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>
					 <div class="form-group text-center">
					 <table class='table table-bordered'><thead><tr bgcolor='#6495ed'><th><font color="#FFF"><b>Total No Of CBM</b></font></th>
					 <th><font color="#FFF"><b>Charge Calculation</b></font></th><th><font color="#FFF"><b>Action</b></font></th></tr></thead>
		             <tr><td><?= $form->field($model2, 'cbm')->label(false) ?></td>
			         <td><?= $form->field($model2, 'pcalc')->dropDownList(['LATER'=>'LATER','NOW'=>'NOW'])->label(false) ?></td>
			<td><button type="submit" name ="btnCharge" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Ok </span></button></td>
			</tr></table>		
                
                
            </div> 
					

					<?php } ?><hr />
		
		
		<table class='table table-bordered'><thead><tr bgcolor='grey'>
		<th>Group Name</th><th>Item Name</th><th>No Of Packages</th><th>No Of Pieces</th><th>Other Description</th><th>Action</th></tr></thead>
		<tr><td width=25%>
		<?= $form->field($model, 'gid')->widget(Select2::classname(), [
            'data' => $model->getGitems($gid),'options' => ['placeholder' => 'Select'],
            ])->label(false) ?>
		</td>
		
		<td width=25%><?= $form->field($model, 'iid')->widget(DepDrop::classname(), ['type'=>DepDrop::TYPE_SELECT2,
                'data'=>$model->getItems(),'options' => ['placeholder' => 'Select'],
                'pluginOptions'=>[
                'depends'=>['orderitems-gid'],
                'url'=>Url::to(['/operation/getsitems'])
               ]
             ])->label(false) ?></td>
			<td width=10%><?= $form->field($model, 'nop')->label(false) ?></td>
			<td width=10%><?= $form->field($model, 'nopieces')->label(false) ?></td>
			<td width=25%><?= $form->field($model, 'descr')->label(false) ?></td>
			<td><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add </span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile('@web/js/enable-disable.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
	

