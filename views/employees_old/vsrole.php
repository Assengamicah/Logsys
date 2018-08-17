<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Employees */

$this->title = $model->emp($model->userid)."'s Role";
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php foreach (Yii::$app->session->getAllFlashes() as $message):; ?>
            
        <?php endforeach; ?>

<div class="employees-view">
	
	<p>
        <?= Html::a('Company Staff Roles', ['userrole'], ['class' => 'btn btn-success']) ?> 
		<?= Html::a('Update Staff Role', ['upsrole', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
		<?= Html::a('Terminate This Role', ['upstaffrole', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'id',
			[
				'label' => 'Staff',
				'value' => $model->emp($model->userid),
			],
			[
				'label' => 'Role',
				'value' => $model->Role($model->rid),
			],
            [
				'label' => 'From',
				'value' => $model->format($model->fdate),
			],
            [
				'label' => 'From',
				'value' => $model->format($model->tdate),
			],
            'status',
            'cby',
            [
				'label' => 'Created Date',
				'value' => $model->format1($model->cdate),
			],
        ],
    ]) ?>

</div>
