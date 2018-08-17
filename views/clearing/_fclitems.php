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
            <b>Container Loding Items Panel.</b></font>
        </h4>
	<?php echo $tbDet; ?><hr /><br />
	
	 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,
	 'enableAjaxValidation'=>false,
	 'fieldConfig' => [
                        'template' => "{input}",
                        'options' => [
                            'tag'=>false
                        ]
    ]
	 ]); ?>
       

					<?php if($hasItems) { ?>
                    <?php echo $tbInv ?>
					

					<?php } ?><hr />
		
		
		<table class='table table-bordered table-gray'><thead><tr><th><b>Control Number</b></th><th><b>Item Group</b></th>
		<th><b>Items</b></th><th><b>CBM</b></th><th><b>Price</b></th><th><b>Client</b></th><th><b>Shipping As</b></th><th><b>Pick</b></th></tr></thead>
		<?php foreach($models as $i=>$model)
		{ 
		   $cbm = 'NA'; 
		   if($model[2] == 'Y')
		    { 
		      $cbm = $model[3]; 
		    } 
			
			$data = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($model[1]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($data as $dt)
					  {
						  $it .= $dt[0].' , ';
						
					  }
					$it = rtrim($it,' , ');
			
			?>
		<tr><td><?php echo $model[0] ?><?php echo $form->field($model[9],"[$i]id")->hiddenInput()->label(false); ?></td><td><?php echo $model[7] ?></td><td><?php echo $it; ?></td><td><?php echo $cbm ?></td><td><?php echo $model[4] ?></td><td><?php echo $model[5] ?></td><td><?php echo $model[6] ?></td>
		    <td align=center><?php echo $form->field($model[9], "[$i]picked")->checkBox(['uncheck' => 0])->label(false) ?></td></tr>
		<?php } ?>
		
			<tr><td colspan=8 align=right><button type="submit" name ="btnAdd" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Add Selected</span></button></td></tr></table>		
   
       	
		
		
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
<?php
$this->registerJsFile(
        '@web/js/enable-disable.js', ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
	

