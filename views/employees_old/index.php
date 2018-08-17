<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\widgets\Growl;

$this->title = 'Employees';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php foreach (Yii::$app->session->getAllFlashes() as $message):; ?>
            
        <?php endforeach; ?>
		
		
<div class="employees-index">

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

            [
				'class' => 'yii\grid\ActionColumn',
				'template'=>'{viewstaff} | {updatestaff} | {terminatestaff}',
				'buttons' => 
				[
					'viewstaff' => function ($url, $model) {     
                                return Html::a('View', $url, [
                                        'title' => Yii::t('yii', 'View Staff'),
                                ]);                                
            
                              },
					'updatestaff' => function ($url, $model) {     
                                return Html::a('Update', $url, [
                                        'title' => Yii::t('yii', 'Update Staff'),
                                ]);                                
            
                              },
					'terminatestaff' => function ($url, $model) {     
                                return Html::a('Terminate', $url, [
                                        'title' => Yii::t('yii', 'Terminate Staff'),
									'data' => [
                    'confirm' => 'Are sure you want to terminate this staff ? The staff wont be recognised as this company staff by the system no more.',
                    'method' => 'post',
                ],
                                ]);                                
            
                              }
				]
			],
        ],
		'tableOptions' =>['class' => 'table table-bordered table-gray'],
    ]); ?>
</div>
