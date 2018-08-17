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
            <b>Container BL Filling Form.</b></font>
        </h4><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>
					 <div class="form-group">
					 <table class='table table-bordered'><thead><tr bgcolor='#6495ed'>
		<th width=36%><font color="#FFF"><b>BL Type</b></font></th><th><font color="#FFF"><b>BL Number</b></font></th><th><font color="#FFF"><b>Action</b></font></th></tr></thead>
		<tr><td><?= $form->field($model2, 'bltype')->widget(Select2::classname(), [
            'data' => ['BILL OF LADING'=>'BILL OF LADING','AIR BILL'=>'AIR BILL'],
			'options' => ['placeholder' => 'Select'],'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
		    <td><?= $form->field($model2, 'blno')->label(false) ?></td>
			<td><button type="submit" name ="btnAdd2" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> OK </span></button></td></tr></table>		
                
            </div> 

					<?php } ?><hr />
		
		
		<table class='table table-bordered table-gray'><thead><tr><th><b>Shipping Line</b></th><th><b>Container #</b></th>
		<th><b>Total Cargos</b></th><th><b>Total Amount(USD)</b></th><th><b>Exp. Arrival Date</b></th><th><b>Pick</b></th></tr></thead>
		<?php foreach($models as $i=>$model){ ?>
		<tr><td><?php echo $model[0] ?><?php echo $form->field($model[6],"[$i]scode")->hiddenInput()->label(false); ?></td>
		<td><?php echo $model[1] ?></td><td align='center'><?php echo number_format($model[4]) ?></td><td align='center'><b><?php echo number_format($model[5],2) ?></b></td><td><?php echo $model[3] ?></td>
		    <td align=center><?php echo $form->field($model[6], "[$i]picked")->checkBox(['uncheck' => 0])->label(false) ?></td></tr>
		<?php } ?>
		
			<tr><td colspan=6 align=right><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add To Manifest</span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile(
        '@web/js/enable-disable2.js', ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
	

