<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Certificates and Permits Form&nbsp;&nbsp;|&nbsp;&nbsp;Documents should be in pdf,jpg or png format and Size Must Not Exceed 4MB.</b></font>
        </h4>
		<table class='table table-bordered'><tr bgcolor="#B34C00"><td><font color="#FFFFFF"><b>Customer Name:</b>&nbsp;<?php echo $cname; ?>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Job ID:&nbsp;<b><?php echo $model->jid; ?></b></font></td><td align="right"><font color="#FFFFFF">Current Exchange Rate:&nbsp;<b><?php echo number_format(Yii::$app->user->identity->rate,2); ?></font></b></td></tr></table>
		<hr />
    <?php $form = ActiveForm::begin(['id'=>'form_member_upload','enableClientValidation'=>false,'type' => ActiveForm::TYPE_HORIZONTAL,
		'options' => ['enctype'=>'multipart/form-data'],
		'formConfig' => ['labelSpan' => 4,'deviceSize' => ActiveForm::SIZE_SMALL]]); ?>
                   
    <div class="row">
        <div class="col-md-6">
		<?= $form->field($model, 'docid')->widget(Select2::classname(), [
         'data' =>$model->getCert($model->jid),'options' => ['placeholder' => 'Select'],'pluginOptions' => ['allowClear' => true,],]) ?>
			<?= $form->field($model, 'charges') ?>
			<?= $form->field($model, 'paidby')->widget(Select2::classname(), [
         'data' =>$model->getPBy($model->jid),'options' => ['placeholder' => 'Select'],'pluginOptions' => ['allowClear' => true,],]) ?>
		 <?= $form->field($model, 'docattach')->widget(FileInput::classname(), [
                             'pluginOptions'=>['allowedFileExtensions'=>['jpg','pdf','png'],'width' => '300px','showCaption' => false,'showRemove' => false,'showCancel' => false,'showUpload' => false,'browseClass' => 'btn green btn-block','browseIcon' => '<i class="glyphicon glyphicon-folder-open"></i> ','browseLabel' => 'Browse...']
			]); ?>
       </div>
	   <div class="col-md-6">
			<?= $form->field($model, 'hascharges')->widget(Select2::classname(), [
            'data' => ['Y'=>'YES','N'=>'NO'],'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			<?= $form->field($model, 'paidin')->widget(Select2::classname(), [
            'data' => ['USD'=>'USD','TZS'=>'TZS'],'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			<?= $form->field($model, 'hasvat')->widget(Select2::classname(), [
            'data' => ['Y'=>'YES','N'=>'NO'],'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
			
       </div>
    </div>
	
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnRegister" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Save </span>
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>
	
	
	

