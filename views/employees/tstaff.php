<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\widgets\Growl;

$this->title = 'Employees';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php foreach (Yii::$app->session->getAllFlashes() as $message):; ?>
            
        <?php endforeach; ?>
		
		
<div class="panel">
    <div class="panel-heading">
	
    <p>
		<?= Html::a('Register Staff', ['create'], ['class' => 'btn green']) ?>
        <?= Html::a('Company Staffs', ['index'], ['class' => 'btn green']) ?> 
    </p>
	
	<h4 class="panel-title text-bold-500">
            <b>Terminated / Suspended Staffs </b>
        </h4>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'empid',
            'empcode',
            'fname',
            'mname',
            'sname',
			'country',
			//'jobtitle',
			//'reportsto',
			//'zone',
			
			[
				'label'=>'Action',
				'format'=>'raw',
				'value' => function ($data) {     
							return Html::a('Reinstate', ['employees/reinstate','id'=>$data['empid']],['data' => [
											'confirm' => 'Are sure you want to reistate this staff ? ',
											'method' => 'post',
										  ]]);                                
		
				},
			],

           
        ],
    ]); ?>
	 </div>
	 
	</div>