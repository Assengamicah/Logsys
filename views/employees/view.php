<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Employees */

$this->title = $model->emp($model->empid);
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?><br /><br />

<?php if (Yii::$app->session->hasFlash('usuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('usuccess'); ?>
        </div>
	 <?php } ?>

<div class="panel">
    <div class="panel-heading">
	
	<p>
		<?= Html::a('Company Staffs', ['index'], ['class' => 'btn green']) ?>
        <?= Html::a('Register Staff', ['create'], ['class' => 'btn green']) ?> 
		<?= Html::a('Update Staff', ['updatestaff', 'id' => $model->empid], ['class' => 'btn green']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'empid',
            //'empcode',
            'fname',
            'mname',
            'sname',
            'gender',
            'email:email',
			'telno',
			[
				'label' => 'Country Of Work',
				'value' => $model->country($model->cid),
			],
            'status',
            [
				'label' => 'Created By',
				'value' => $model->emp($model->cby),
			],
            [
				'label' => 'Created date',
				'value' => $model->format1($model->cdate),
			],
        ],
    ]) ?>

	 </div>
	 
	</div>
