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
            <b>Order Items to Be Squeezed Form.</b></font>
        </h4>
	<?php echo $tbDet; ?><hr /><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>
					 <div class="form-group text-center">
					 <table class='table table-bordered'><thead><tr bgcolor='#6495ed'>
		<th width=86%><font color="#FFF"><b>Bundle Item Group</b></font></th><th><font color="#FFF"><b>Action</b></font></th></tr></thead>
		<tr><td><?= $form->field($model2, 'iid')->widget(Select2::classname(), [
            'data' => $model2->getItems()])->label(false) ?></td>
			<td><button type="submit" name ="btnAdd2" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Update </span></button></td></tr></table>		
                
            </div> 

					<?php } ?><hr />
		
		
		<table class='table table-bordered'><thead><tr bgcolor='grey'>
		<th width=82%>Item Name</th><th>Action</th></tr></thead>
		<tr><td><?= $form->field($model, 'iid')->widget(Select2::classname(), [
            'data' => $this->context->getSQIB($cid),'options' => ['placeholder' => 'Select'],
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
	

