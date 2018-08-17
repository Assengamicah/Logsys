<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\grid\GridView;

$this->title = 'Role Assignment Form';
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['roles']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php foreach (Yii::$app->session->getAllFlashes() as $message):; ?>
            
        <?php endforeach; ?>

<div class="role-assign">

    <?php $form = ActiveForm::begin(); ?>
	
	<table style = "border-collapse: separate; border-spacing: 20px;">

    <tr>
	<td><?= $form->field($model, 'userid')->dropDownList($model->Users(),['prompt' => 'Select', 'style'=>'width:250px']) ?></td>
	
	<td><?= $form->field($model, 'rid')->dropDownList($model->getRole(),['prompt' => 'Select', 'style'=>'width:250px']) ?></td>
	
	<td><?= $form->field($model, 'fdate')->widget(DatePicker::classname(),[
		'name' => 'fdate',
		'type' => DatePicker::TYPE_INPUT,	
		//'value' => date('d-M-Y', strtotime('+2 days')),
		'options' => ['placeholder' => 'Role Start Date'],
		'pluginOptions' => [
			'format' => 'dd/mm/yyyy',
			'todayHighlight' => true
		]
		]);
		
		?></td>
	
	<td><?= $form->field($model, 'tdate')->widget(DatePicker::classname(),[
		'name' => 'tdate',
		'type' => DatePicker::TYPE_INPUT,	
		//'value' => date('d-M-Y', strtotime('+2 days')),
		'options' => ['placeholder' => 'Role End Date'],
		'pluginOptions' => [
			'format' => 'dd/mm/yyyy',
			'todayHighlight' => true
		]
		]);
		
		?></td>
	</tr>
	
	</table>

    <div class="form-group">
       <center><?= Html::submitButton('Update Staffrole', ['class' => 'btn btn-success']) ?></center>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<div>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
			'staff',
            'role',
            'startdate',
            'enddate',

				[
					'label'=>'Action',
					'format'=>'raw',
					'value' => function ($data) {     
                                return Html::a('View', ['employees/vsrole','id'=>$data['id']]).' | '.Html::a('Update', ['employees/upsrole','id'=>$data['id']])
								.' | '.Html::a('Terminate', ['employees/tsrole','id'=>$data['id']], ['data' => [
											'confirm' => 'Are you sure you want to terminate this staff role?',
											'method' => 'post',
										  ]]);                                
            
                    },
			    ],
		],
        
    ]); ?>

</div>
