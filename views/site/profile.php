<?php

/* @var $this yii\web\View */

$this->title = 'Dashboard';

$this->params[ 'breadcrumbs' ][] = 'Dashboard';
?>

<div class="content">

    <!-- Simple panel -->
    <div class="panel panel-flat">
        <div class="panel-heading">
            <h5 class="panel-title">System User Profile</h5><hr />
			 <?php if (Yii::$app->session->hasFlash('usuccess')){ ?>
              <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('usuccess'); ?>
        </div>
	 <?php } ?>	
            <?php echo $tbData; ?>
        </div>

       
    <!-- /simple panel -->


</div>
</div>
