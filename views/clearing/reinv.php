<?php

use yii\helpers\Html;
//use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use kartik\widgets\ActiveForm;
use kartik\widgets\Typeahead;
use yii\helpers\Url;


$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">

    <div class="panel">
        <div class="panel-heading">
           
            
       
		 <?php  $form = ActiveForm::begin([
            'id' => 'login2-form-horizontal', 
            'type' => ActiveForm::TYPE_HORIZONTAL,
			'enableClientValidation'=>false,
			'enableAjaxValidation'=>false,
            
        ]); 
      ?>     
        <table class="table table-gray" id="myid">
            <thead>
            <tr>
                
				<th width="44%"><font size='+1'><b>Clearing Module - Reprint Invoice</b></font></th>
				<th align="right">
                   
	 
             <?= $form->field($model, 'invno',['addon' => ['append' => ['content' => Html::submitButton('Reprint Invoice', ['class'=>'btn primary','name'=>'btnReprint']), 'asButton' => true]]])->textInput(['autofocus' => true,'placeholder'=>'Search Invoice By Providing Invoice Number'])->label(false); ?>
						 
                </th>
                
            </tr></thead>
           
           
        </table>
		
		 <?php ActiveForm::end(); ?>
		</div>
		<?php if(Yii::$app->session->hasFlash('error')){ ?>
            <div class="alert alert-error" role="alert">
                <?php echo Yii::$app->session->getFlash('error'); ?>
            </div>
        <?php } ?>
       <hr /> 
	   
  
	</div>
	
</div>