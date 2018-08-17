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
            <b>Clearing Job Registration Form.</b></font>
        </h4><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>
					 <div class="form-group">
					 <table class='table table-bordered'><thead><tr bgcolor='#6495ed'>
		<th><font color="#FFF"><b>Customer Name</b></font></th><th><font color="#FFF"><b>ICD</b></font></th><th width=14%><font color="#FFF"><b>Tansad #</b></font></th><th><font color="#FFF"><b>Job Handled By</b></font></th><th><font color="#FFF"><b>Action</b></font></th></tr></thead>
		<tr><td><?= $form->field($model2, 'client')->widget(Select2::classname(), ['data' =>$model2->getClients(),
            'options' => ['placeholder' => 'Select'],'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
			<td><?= $form->field($model2, 'icd')->widget(Select2::classname(), ['data' =>$model2->getICD(),
            'options' => ['placeholder' => 'Select'],'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
			 <td><?= $form->field($model2, 'tansno')->label(false) ?></td>
			<td><?= $form->field($model2, 'cfid')->widget(Select2::classname(), ['data' =>$model2->getCF(),
            'options' => ['placeholder' => 'Select'],'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
			<td><button type="submit" name ="btnAdd2" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> OK </span></button></td></tr></table>		
                
            </div> 

					<?php } ?><hr />
		
		
		<table class='table table-bordered table-gray'><thead><tr><th><b>Shipping Line</b></th><th><b>BL #</b></th><th><b>Container #</b></th>
		<th><b>Total Cargos</b></th><th><b>Exp. Arrival Date</b></th><th><b>Pick</b></th></tr></thead>
		<?php foreach($models as $i=>$model){ ?>
		<tr><td><?php echo $model[0] ?><?php echo $form->field($model[6],"[$i]containerno")->hiddenInput()->label(false); ?></td>
		<td><?php echo $model[1] ?></td><td><?php echo $model[2] ?></td><td align='center'><?php echo number_format($model[5]) ?></td><td><?php echo $model[4] ?></td>
		    <td align=center><?php echo $form->field($model[6], "[$i]picked")->checkBox(['uncheck' => 0])->label(false) ?></td></tr>
		<?php } ?>
		
			<tr><td colspan=6 align=right><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add To The Clearing Job</span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile(
        '@web/js/enable-disable2.js', ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
	

