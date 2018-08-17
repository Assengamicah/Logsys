<?php

/* @var $this yii\web\View */
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Client Profiles';

?>



<div class="panel">
    <div class="panel-heading">
	 <?php if (Yii::$app->session->hasFlash('asuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('asuccess'); ?>
        </div>
	 <?php } ?>
	  <?php if (Yii::$app->session->hasFlash('nsuccess')){ ?>
        <div class="alert alert-error">
            <?php echo Yii::$app->session->getFlash('nsuccess'); ?>
        </div>
	 <?php } ?>	  
        <h4 class="panel-title text-bold-500">
            <b>Registered Addendums</b><hr />
        </h4>
        
    
    <div class="table-responsive">

        <?php
					  	  
					   echo  GridView::widget(['dataProvider' => $dataProvider,
					    'emptyText' => '<b><i>There is no any Registered Addendum at the Moment..</i></b>',
						'columns' => [
						'Sn',
						'Supplier',
						'Order No',
						'Order Date',
						'Delivery Date',
						'Order Status',
						
						[
								   'label' => 'View',
								   'format' => 'raw',
								   'value' => function ($data) {
												 return Html::a('View Details',['operation/showorder', 'oid' => $data['Order No']]);
											 },
						],
						[
								   'label' => 'Cancel',
								   'format' => 'raw',
								   'value' => function ($data) {
												 return Html::a('Cancel',['operation/corder', 'oid' => $data['Order No']]);
											 },
						],
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); 
					  
                    ?>

    </div>
	</div>
</div>




