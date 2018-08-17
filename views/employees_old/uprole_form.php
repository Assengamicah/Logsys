<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

$this->title = 'Roles Update Form';
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['roles']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="role-create">

    <h2><?= Html::encode($this->title) ?></h2>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['style'=>'width:400px']) ?>

    <div class="form-group">
       <?= Html::submitButton('Update Role', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<div>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
			
				[
					'label'=>'Action',
					'format'=>'raw',
					'value' => function ($data) {     
                                return Html::a('View', ['employees/view','id'=>$data['rid']]).' | '.Html::a('Update', ['employees/update','id'=>$data['rid']]);                                
            
                    },
			    ],
        ],
    ]); ?>


</div>
