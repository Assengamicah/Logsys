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
     <?php if (Yii::$app->session->hasFlash('osuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('osuccess'); ?>
        </div>
	 <?php } ?>	     
	 <?php echo $tbData ?>
	 <hr />
	</div>
	</div>

