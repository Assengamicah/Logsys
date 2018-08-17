<?php

use yii\helpers\Html;
//use yii\bootstrap\ActiveForm;
use kartik\widgets\ActiveForm;


?>
<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            Exchange Rate<hr />
        </h4>
        
       <?php if (Yii::$app->session->hasFlash('rate')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('rate'); ?>
        </div>
	 <?php } ?>	
    
	 </div>
	 
	</div>
	

