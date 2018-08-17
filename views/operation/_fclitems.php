<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\widgets\ActiveForm;
use yii\grid\GridView;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;
use kartik\widgets\Typeahead;
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
					
					<table class="table table-gray" id="myid">
				<thead>
					<tr>
						
						<th align="right">
							<?= $form->field($mod, 'jid')->textInput(['autofocus' => true,'placeholder'=>'Search Job By Providing Job ID / BL Number / Container Number'])->widget(Typeahead::classname(),[
							'options' => ['placeholder' => 'Search Cargo By Providing Control Number'],
							'scrollable' => true,
							'pluginOptions' => ['highlight'=>true],
							'dataset' => [
								[
								'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
								'display' => 'value',
								'remote' => [
								'url' => Url::to(['operation/rcargo']) . '?q=%QUERY',
								'wildcard' => '%QUERY'
								]
								]
							]
							])->label(false); ?> 
						</th><th width="10%" align="right"><b><button type="submit" name ="btnSearch" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Search </span></button></b></th>
					</tr>
				</thead>
			</table><hr />
			
			<?php if(!empty($tbJob)) { ?>
		<?php echo $tbJob ?>
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
			$cnt = 1;
			$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$model[0]'")->queryScalar();
			if($ccn)
			{
				$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
			}
			$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$model[0]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= '<b>'.$rs[0].'</b>: Items : '.$rs[2].' : Descr : '.$rs[3].' , ';
						
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
	

