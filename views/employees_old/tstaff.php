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
		<?= Html::a('Register Staff', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Company Staffs', ['index'], ['class' => 'btn btn-success']) ?> 
		<?= Html::a('Terminated / Suspended Staffs', ['tstaff'], ['class' => 'btn btn-success']) ?>
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
				'template'=>'{reinstate}',
				'buttons' => 
				[
					'reinstate' => function ($url, $model) {     
                                return Html::a('Reinstate Staff', $url, [
                                        'title' => Yii::t('yii', 'Reinstate Staff'),
                                ]);                                
            
                              },
				]
			],
        ],
    ]); ?>
</div>