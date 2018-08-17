<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;

//use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            Shipping Items Registration/Updation Form<hr />
        </h4>
      
    <?php $form = ActiveForm::begin(['layout'=>'horizontal','enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>
	 <?= $form->field($model, 'gid')->widget(Select2::classname(), [
            'data' => $model->getGname(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
	                     <?= $form->field($model, 'name') ?>
	                    
                         
				
			 <div class="form-group text-center">
                <button type="submit" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Save </span> 
                </button>
            </div>
        
   
    <?php ActiveForm::end(); ?>
	 <hr />
	  <?php if (Yii::$app->session->hasFlash('isuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('isuccess'); ?>
        </div>
	 <?php } ?>	
	  <div class="table-responsive">

        <?php
					  	  
					  echo $tbGI;
					  /* echo  GridView::widget(['dataProvider' => $dataProvider,
					    'emptyText' => '<b><i>There is no any Registered Shipping Item at the Moment..</i></b>',
						'layout' => '{items}{pager}',
						'columns' => [
						'Sn',
						'Item Name',
						'Charged Per',
						'Rate - USD',
						'Registered By',
						
						
						[
								   'label' => 'Update',
								   'format' => 'raw',
								   'value' => function ($data) {
												 return Html::a('Update',['employees/eitem', 'iid' => $data['iid']]);
											 },
						],
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); */
					  
                    ?>

    </div>
	 </div>
	 
	</div>
	

