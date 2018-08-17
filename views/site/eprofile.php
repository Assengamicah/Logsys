<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
use yii\bootstrap\ActiveForm;
$this->title = 'Dashboard';

$this->params[ 'breadcrumbs' ][] = 'Mail Account';
?>

<div class="content">

    <!-- Simple panel -->
    <div class="panel panel-flat">
        <div class="panel-heading">
            <h5 class="panel-title">Employee Profile Update Form&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo Html::a("<b>Home</b>",['site/index']) ?></h5><hr />
             <?php if (Yii::$app->session->hasFlash('esuccess')){ ?>
              <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('esuccess'); ?>
        </div>
	 <?php } ?>	

        <?php $form = ActiveForm::begin(['layout' => 'horizontal','enableClientValidation'=>false,'enableAjaxValidation'=>false,
	]); ?>
                   
    <div class="row">
        <div class="col-md-12">
			<?= $form->field($model, 'fname') ?>
			<?= $form->field($model, 'mname') ?>
			<?= $form->field($model, 'sname') ?>
			<?= $form->field($model, 'email') ?>
			<?= $form->field($model, 'telno') ?>
			<?= $form->field($model, 'uname') ?>
			<?= $form->field($model, 'passwd')->passwordInput() ?>
			<?= $form->field($model, 'cpwd')->passwordInput() ?>
       </div>
	   
    </div>
	                     
			 <div class="form-group text-center">
     <button type="submit" name ="btnGeneral" class="btn grayish btn-sm">Update Profile &nbsp; <i class="fa fa-arrow-right"></i></button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
    
<hr />


</div>
</div>
