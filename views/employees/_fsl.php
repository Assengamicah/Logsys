<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;

//use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            Shipping Line Registration/Updation Form<hr />
        </h4>
      
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL,'enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>
	<div class="row">
        <div class="col-md-6">
	                     <?= $form->field($model, 'name') ?>
						<?= $form->field($model, 'address') ?>
			 <?= $form->field($model, 'email') ?>
         </div>                
			 <div class="col-md-6">
	                    <?= $form->field($model, 'paddress') ?>
						<?= $form->field($model, 'phone') ?>
			<div class="form-group text-center">
                <button type="submit" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Save </span> 
                </button>
            </div>
         </div>        
</div>		 
			 
        
   
    <?php ActiveForm::end(); ?>
	 <hr />
	  <?php if (Yii::$app->session->hasFlash('slsuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('slsuccess'); ?>
        </div>
	 <?php } ?>	
	  <div class="table-responsive">

        <?php
					  	  
					   echo  GridView::widget(['dataProvider' => $dataProvider,
					    'emptyText' => '<b><i>There is no any Registered Shipping Line at the Moment..</i></b>',
						'layout' => '{items}{pager}',
						'columns' => [
						'Sn',
						'Name',
						'Physical Address',
						'Postal Address',
						'Phone',
						'Email',
						
						
						[
								   'label' => 'Action',
								   'format' => 'raw',
								   'value' => function ($data) {
												 return Html::a('Update',['employees/esline', 'slid' => $data['slid']]);
											 },
						],
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); 
					  
                    ?>

    </div>
	 </div>
	 
	</div>
	

