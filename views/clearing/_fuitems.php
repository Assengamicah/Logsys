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
            <b>Item Price Update Form.</b></font>
        </h4>
	<?php echo $tbDet; ?><hr /><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>

					<?php } ?><hr />
		
		
		 <table class='table table-bordered'><thead><tr bgcolor='#6495ed'>
		<th width=60%><font color="#FFF"><b>Items</b></font></th><th><font color="#FFF"><b>Total CBM</b></font></th>
		<th><font color="#FFF"><b>Action</b></font></th></tr></thead>
		<tr><td><?= $form->field($model, 'iid')->widget(Select2::classname(), [
            'data' => $this->context->getSQI2($id)])->label(false) ?></td>
			<td><?= $form->field($model, 'cbm')->label(false) ?></td>
			<td><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Calculate </span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile(
        '@web/js/enable-disable3.js', ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
	

