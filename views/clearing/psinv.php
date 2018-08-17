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
            <b>Container Invoice Creation Form.</b></font>
        </h4>
		<table class='table table-bordered'><tr bgcolor="#B34C00"><td><font color="#FFFFFF">&nbsp;&nbsp;&nbsp;Job ID:&nbsp;<b><?php echo $jid; ?></b></font></td><td align="right"><font color="#FFFFFF">Current Exchange Rate:&nbsp;<b><?php echo number_format(Yii::$app->user->identity->rate,2); ?></font></b></td></tr>
		<tr><td>&nbsp;Invoice to be issued to:&nbsp;<b><?php echo $aname ?></b></td><td align='right'>&nbsp;Date:&nbsp;<b><?php echo date("d/m/Y") ?></b></td></tr></table>
		<hr />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbPay ?>
					 <div class="form-group">
					 <table class='table table-bordered'><thead><tr bgcolor='#6495ed'>
		<th width=36%><font color="#FFF"><b>Additional Charges</b></font></th><th><font color="#FFF"><b>Amount</b></font></th><th><font color="#FFF"><b>Has VAT?</b></font></th><th><font color="#FFF"><b>Currency</b></font></th><th><font color="#FFF"><b>Action</b></font></th></tr></thead>
		<tr><td><?= $form->field($model2, 'fid')->widget(Select2::classname(), [
            'data' => $this->context->getFC(),'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
		    <td><?= $form->field($model2, 'amt')->label(false) ?></td>
			<td><?= $form->field($model2, 'hasvat')->widget(Select2::classname(), [
            'data' =>['Y'=>'YES','N'=>'NO'],'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
			<td><?= $form->field($model2, 'paidin2')->widget(Select2::classname(), [
            'data' =>['TZS'=>'TZS','USD'=>'USD'],'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
			<td><button type="submit" name ="btnAdd2" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add Charges </span></button></td></tr></table>		
                
            </div> 

					<?php } ?><hr />
		
		
		<table class='table table-bordered table-gray'><tr bgcolor="#0B67CD"><td width='86%'><font color="#FFFFFF"><b>CONTAINER #</b></font></td><td><font color="#FFFFFF"><b>ACTION</b></font></td></tr>
		<tr><td><?= $form->field($model, 'iid')->widget(Select2::classname(), [
            'data' => $this->context->getCItem(),'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])->label(false) ?></td>
		<td><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add </span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile(
        '@web/js/enable-disable2.js', ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
	

