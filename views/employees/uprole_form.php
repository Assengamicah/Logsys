<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;
use yii\grid\GridView;


/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?><br /><br /><br />

<?php foreach (Yii::$app->session->getAllFlashes() as $message):; ?>
            
        <?php endforeach; ?>
		

<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Role Registration Form.</b>
        </h4>
       
		<?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,
		]); ?>
                   
		<div class="row">
			<div class="col-md-12">
				<?= $form->field($model, 'name')->textInput(['style'=>'width:500px']) ?>
			</div>
		</div>
	                     
		 <div class="form-group">
			<button type="submit" name ="btnRegister" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Update Role </span> <i class="icon-arrow-right13"></i>
			</button>
		</div> 

			
   
		<?php ActiveForm::end(); ?>
	</div>
	 
</div>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
			
				[
					'label'=>'Action',
					'format'=>'raw',
					'value' => function ($data) {     
                                return Html::a('View', ['employees/view','id'=>$data['rid']]).' | '.Html::a('Update', ['employees/update','id'=>$data['rid']]);                                
            
                    },
			    ],
        ],
    ]); ?>


</div>
