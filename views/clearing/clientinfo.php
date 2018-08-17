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
	<?php if (Yii::$app->session->hasFlash('ssuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('ssuccess'); ?>
        </div>
	 <?php } ?>	
        
	 <?php echo $this->context->getClientDetails($cid); ?>
	 <hr />
	</div>
	</div>

