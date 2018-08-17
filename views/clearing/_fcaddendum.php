<?php

use yii\helpers\Html;
use yii\helpers\Url;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;

?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Addendum Cancel Form </b></font></h4>
            <?php echo $tbData; ?>
      <hr />		
    <?php  $form = ActiveForm::begin([
            'id' => 'login2-form-horizontal', 
            'type' => ActiveForm::TYPE_VERTICAL,
			'enableClientValidation'=>false,
			'enableAjaxValidation'=>false,
        ]); 
      ?>                  
	<div class="row">
       <div class="col-md-12">
                         <?= $form->field($model, 'creason')->widget(\yii\redactor\widgets\Redactor::className(), [
        'clientOptions' => [
        //'imageUpload' => Url::to(['/redactor/upload/image']),
		'buttons' => ['format','bold', 'italic','orderedlist','unorderedlist','horizontalrule','underline', 'indent', 'outdent'],
		'plugins' => [
                    'fontfamily',
                    'fontsize',
                    'fontcolor',
                ],

]]) ?>
		</div>				                        
	 
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnContact" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Cancel </span> 
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>

	</div>
	

