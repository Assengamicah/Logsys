<?php

/* @var $this yii\web\View */
use yii\grid\GridView;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = 'Client Profiles';

?>



<div class="panel">
    <div class="panel-heading">
	 <?php if (Yii::$app->session->hasFlash('asuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('asuccess'); ?>
        </div>
	 <?php } ?>
        <h4 class="panel-title text-bold-500">
		 <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL,
		'enableClientValidation'=>false,'enableAjaxValidation'=>false,'validateOnSubmit' => false,
		]); ?>
		<table>
		<tr><td width='70%'><b>Registered Suppliers</b></td><td align=right><?= $form->field($model, 'name',['addon' => ['append' => [
                    'content' => Html::submitButton('Search', ['class'=>'btn primary','name'=>'btnSName']), 'asButton' => true]]])->textInput(['placeholder' => "Search by Full Name",'style'=>'width:300px'])->label(false); ?></td></tr></table>
         <?php ActiveForm::end(); ?>
            <hr />
        </h4>
        
    
    <div class="table-responsive">

        <?php
					  	  
					   echo  GridView::widget(['dataProvider' => $dataProvider,
					    'emptyText' => '<b><i>There is no any Registered Client at the Moment..</i></b>',
						'layout' => '{items}{pager}',
						'columns' => [
						'Sn',
						'Client Name',
						'Phone Number',
						'Email',
						'Client Type',
						'Registered Date',
						
						[
								   'label' => 'Action',
								   'format' => 'raw',
								   'value' => function ($data) {
												 return Html::a('View/Update',['operation/editcl', 'cid' => $data['cid']]);
											 },
						],
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); 
					  
                    ?>

    </div>
	</div>
</div>




