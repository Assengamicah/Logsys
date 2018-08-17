<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\widgets\Growl;

$this->title = 'Employees';
$this->params['breadcrumbs'][] = $this->title;
?><br /><br />

<?php if (Yii::$app->session->hasFlash('ssuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('ssuccess'); ?>
        </div>
	 <?php } ?>
		
		
<div class="panel">
    <div class="panel-heading">
    <p>
		<?= Html::a('Register Staff', ['create'], ['class' => 'btn green']) ?>
        <?= Html::a('Company Staffs', ['index'], ['class' => 'btn green']) ?> 
		<?= Html::a('Terminated / Suspended Staffs', ['tstaff'], ['class' => 'btn green']) ?>
    </p>

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
			//'reportsto',
			//'office',
			
			[
				'label'=>'Action',
				'format'=>'raw',
				'value' => function ($data) {     
							return Html::a('View', ['employees/viewstaff','id'=>$data['empid']]).' | '.Html::a('Update', ['employees/updatestaff','id'=>$data['empid']]).' | '.Html::a('Terminate', ['employees/terminatestaff','id'=>$data['empid']],['data' => [
											'confirm' => 'Are sure you want to terminate this staff ? The staff wont be recognised as this company staff by the system anymore.',
											'method' => 'post',
										  ]]);                                
		
				},
			],
        ],
		'tableOptions' =>['class' => 'table table-bordered table-gray'],
    ]); ?>
	 </div>
	 
	</div>
