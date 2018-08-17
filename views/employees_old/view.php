<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Employees */

$this->title = $model->emp($model->empid);
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php foreach (Yii::$app->session->getAllFlashes() as $message):; ?>
            
        <?php endforeach; ?>

<div class="employees-view">
	
	<p>
        <?= Html::a('Company Staff', ['index'], ['class' => 'btn btn-success']) ?> 
		<?= Html::a('Register Staffs', ['create'], ['class' => 'btn btn-success']) ?>
		<?= Html::a('Update Staff', ['updatestaff', 'id' => $model->empid], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'empid',
            'empcode',
            'fname',
            'mname',
            'sname',
            'gender',
            'email:email',
			'telno',
			[
				'label' => 'Job Tittle',
				'value' => $model->theTitle($model->titleid),
			],
			[
				'label' => 'Reports To',
				'value' => $model->theTitle($model->reportsto),
			],
            //'pic',
			[
				'label' => 'Zone',
				'value' => $model->theZone($model->zid),
			],
            //'zid',
            //'uname',
            //'pwd',
            //'atoken',
            'status',
            'cby',
            'cdate',
            //'eby',
            //'edate',
            //'llogindate',
        ],
    ]) ?>

</div>
