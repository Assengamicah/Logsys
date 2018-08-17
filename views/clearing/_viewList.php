<?php

use yii\helpers\Html;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */


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
        <table class="table table-gray">
            <thead>
            <tr>
                
				<th width="44%"><font size='+1'><b>Clearing Module</b></font></th>
				<th align="right">
                   
	 
             <?= $form->field($model, 'jid',['addon' => ['append' => ['content' => Html::submitButton('OK', ['class'=>'btn primary','name'=>'btnSNumber']), 'asButton' => true]]])->textInput(['autofocus' => true,'placeholder'=>'Search Job By Providing Job ID / BL Number / Container Number'])->label(false); ?>
						 
                </th>
                
            </tr></thead>
           
           
        </table>
		<?php echo $tbJob; ?>
		 <?php ActiveForm::end(); ?>
		</div>
       <hr /> 
	   
    </div>
	
	<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_view2',
]); ?>
	
</div>