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
						'rowOptions'=>function($data){
							//if($data['Price USD'] == 'LATER')
							//{
								//return ['class' => 'danger'];
							//}
						},
						'columns' => [
						'Order No',
						'Control No',
						[
								   'label' => 'Items',
								   'format' => 'raw',
								   'value' => function ($data) {
									             $cno = $data['Control No'];
												 $q = "SELECT i.name FROM items i INNER JOIN plist p ON i.iid = p.iid WHERE p.cno ='$cno'";
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
												 return Html::a('Repack',['operation/squeeze', 'oid' => $data['Order No']]);
											 },
						],
						[
								   'label' => 'Remarks',
								   'format' => 'raw',
								   'value' => function ($data) {
									            if($data['dueon'] >1)
												{
													return Html::a('Overstayed',['operation/nsorders'],['class'=>'btn red btn-xs']);
												}
												else
												{
													return Html::a('On Track',['operation/nsorders'],['class'=>'btn green btn-xs']);
												}
												 
											 },
						],
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); 
					  
                    ?>

    </div>
	</div>
</div>




