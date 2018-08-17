<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?><br /><br />

<?php if (Yii::$app->session->hasFlash('srolesuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('srolesuccess'); ?>
        </div>
	 <?php } ?>
	 
<?php if (Yii::$app->session->hasFlash('sroleno')){ ?>
        <div class="alert alert-error">
            <?php echo Yii::$app->session->getFlash('sroleno'); ?>
        </div>
	 <?php } ?>
	 
	 <?php if (Yii::$app->session->hasFlash('stsuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('stsuccess'); ?>
        </div>
	 <?php } ?>
	 
	 <?php if (Yii::$app->session->hasFlash('susuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('susuccess'); ?>
        </div>
	 <?php } ?>

<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Role Assignment Form.</b>
        </h4>
       
    <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,
	]); ?>
                   
    <div class="row">
        <div class="col-md-3">
			<?= $form->field($model, 'userid')->widget(Select2::classname(), [
            'data' => $model->Users(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
		</div>
		<div class="col-md-3">
			<?= $form->field($model, 'rid')->widget(Select2::classname(), [
            'data' => $model->getRole(),
            'options' => ['placeholder' => 'Select'],
            'pluginOptions' => ['allowClear' => true,],])  ?>
		</div>
		<div class="col-md-3">
			<?= $form->field($model, 'fdate')->widget(DatePicker::classname(),[ 'pluginOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy','todayHighlight' => true ]]); ?>
		</div>
		<div class="col-md-3">
			<?= $form->field($model, 'tdate')->widget(DatePicker::classname(),[ 'pluginOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy','todayHighlight' => true ]]); ?>
       </div>
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnRegister" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Assign Role </span> <i class="icon-arrow-right13"></i>
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>
	 
	</div>

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
