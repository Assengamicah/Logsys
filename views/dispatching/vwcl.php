<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
     <?php if (Yii::$app->session->hasFlash('clsuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('clsuccess'); ?>
        </div>
	 <?php } ?>	     

	 <hr />
	</div>
	</div>

