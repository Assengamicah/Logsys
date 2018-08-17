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
            <b>Print Cutting Instruction Panel</b><hr />
        </h4>
        
    
    <div class="table-responsive">

        <?php
					  	  
					   echo  GridView::widget(['dataProvider' => $dataProvider,
					    'emptyText' => '<b><i>There is no Cutting Instruction Received At the Moment..</i></b>',
						'columns' => [
						'Supplier',
						'Order No',
						'Instruction Date',
						'Reference No',
						
						[
								   'label' => 'Action',
								   'format' => 'raw',
								   'value' => function ($data) {
									   if($data['isdone'] == 'N')
											  {
								return Html::a('Edit',['manager/cedits1', 'cid' => $data['Reference No']]);
											  }
											  else
											  {
												return 'Cut Update Already Done';  
											  }
											 },
						],
						[
								   'label' => 'Print',
								   'format' => 'raw',
								   'value' => function ($data) {
									  
						        return Html::a('Print',['manager/printco', 'cid' => $data['Reference No'],'isnew'=>'Y'],
								['target'=>'_blank']);  
											  
											 },
						],
						
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); 
					  
                    ?>

    </div>
	</div>
</div>




