<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Employees */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Roles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php foreach (Yii::$app->session->getAllFlashes() as $message):; ?>
            
        <?php endforeach; ?>

<div class="employees-view">
	
	<p>
        <?= Html::a('Create Role', ['roles'], ['class' => 'btn green']) ?> 
		<?= Html::a('Update Role', ['update', 'id' => $model->rid], ['class' => 'btn green']) ?> 
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'rid',
            'name',
            [
				'label' => 'Created By',
				'value' => $model->Emp($model->cby),
			],
            [
				'label' => 'Created Date',
				'value' => $model->format1($model->cdate),
			],
        ],
    ]) ?>

</div>
