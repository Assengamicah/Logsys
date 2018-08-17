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
            <b>Receipt To</b></font>
        </h4>
	<?php echo $tbDet; ?><hr /><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>

					
					
					 <table class='table table-bordered'><thead><tr bgcolor='#6495ed'>
		<th width=46%><font color="#FFF"><b>Additional Charges</b></font></th><th><font color="#FFF"><b>Amount</b></font></th><th><font color="#FFF"><b>Currency</b></font></th><th><font color="#FFF"><b>Action</b></font></th></tr></thead>
		<tr><td><?= $form->field($model2, 'fid')->widget(Select2::classname(), [
            'data' => $this->context->getFC(),'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
		    <td><?= $form->field($model2, 'amt')->label(false) ?></td>
			
			<td><?= $form->field($model2, 'paidin2')->widget(Select2::classname(), [
            'data' =>['TZS'=>'TZS','USD'=>'USD'],'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
			<td><button type="submit" name ="btnAdd2" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add Charges </span></button></td></tr></table>	
			<?php } ?>
					<hr />
		
		
		<table class='table table-bordered'><thead><tr bgcolor='grey'>
		<th width=82%>Item Name</th><th>Action</th></tr></thead>
		<tr><td><?= $form->field($model, 'iid')->widget(Select2::classname(), [
            'data' => $this->context->getSQI(),'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
			<td><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add </span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile(
        '@web/js/enable-disable.js', ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
	

