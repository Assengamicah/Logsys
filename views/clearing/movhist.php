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
            <b>Stock Items Yet to Be Shipped Panel</b><hr />
        </h4>
        
    
    <div class="table-responsive">

        <?php
					  	  
					   echo  GridView::widget(['dataProvider' => $dataProvider,
					    'emptyText' => '<b><i>There is no any Registered Items that has yet been Shipped at the Moment..</i></b>',
						'layout' => '{items}{pager}',
						'columns' => [
						'Order No',
						'Control No',
						[
								   'label' => 'Items',
								   'format' => 'raw',
								   'value' => function ($data) {
									             $iid = $data['iid'];
												 $q = "SELECT name FROM items WHERE iid IN($iid)";
												 $rst = Yii::$app->db->createCommand($q)->queryAll(false);
												 $d = '';
												 foreach($rst as $rs)
												 {
													 $d .= $rs[0].' , ';
												 }
												 return rtrim($d,' ,');
											 },
						],
						'CBM',
						'Price USD',
						'Client',
						'Received Date',
						[
								   'label' => 'Edit',
								   'format' => 'raw',
								   'value' => function ($data) {
									       if($data['pcalc'] == 'NOW')
										   {
												 return Html::a('Edit',['operation/iedits1', 'ono' => $data['Order No']]);
										   }
										   else
										   {
											 return Html::a('Edit',['operation/iedits1','ono' => $data['Order No']]).' | '.
					 Html::a('Insert Price',['operation/iedits2','ono' => $data['Order No'],'id' => $data['id']]);  
										   }
											 },
						],
						[
								   'label' => 'Action',
								   'format' => 'raw',
								   'value' => function ($data) {
												 return Html::a('Squeeze/Repack',['operation/squeeze', 'oid' => $data['Order No']]);
											 },
						],
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); 
					  
                    ?>

    </div>
	</div>
</div>




