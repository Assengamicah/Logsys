<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
     <?php if (Yii::$app->session->hasFlash('jsuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('jsuccess'); ?>
        </div>
	 <?php } ?>	     
	 <?php echo $tbOne ?>
	 <hr />
	</div>
	</div>

