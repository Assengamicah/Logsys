<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

//use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Application */
/* @var $form ActiveForm */
?>
<div class="panel">
<?php if($_SESSION['supid']) { ?>
    <div class="panel-heading">
        <h4 class="panel-title text-bold-500">
            <b>Supplier Contact Person Fill in Form.<font color=blue> For Supplier: <?php echo $_SESSION['sname']; ?></b></font></h4>
			<?php echo $this->context->getCP(); ?><hr />
        
       <?php if (Yii::$app->session->hasFlash('csuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('csuccess'); ?>
        </div>
	 <?php } ?>	
	 		<b>Note: Fill Detail of Supplier Contact Person one by one</b><hr />		
    <?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false,
	]); ?>
                        
	<div class="row">
       <div class="col-md-6">
                          <?= $form->field($model, 'fullname') ?>
						 <?= $form->field($model, 'phone') ?>
						                         </div>
	 <div class="col-md-6">
						 <?= $form->field($model, 'title') ?>
						 <?= $form->field($model, 'email') ?>
						                         </div>
    </div>
	                     
			 <div class="form-group text-center">
                <button type="submit" name ="btnContact" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Save </span> 
                </button>
            </div> 

			
   
    <?php ActiveForm::end(); ?>
	 </div>
	<?php } else { ?>
     <br /><br />
	 <div class="alert alert-warning">
           You cant Fill <b>Supplier Contact Person</b> For Unknown Supplier.
        </div>
<?php } ?>

	</div>
	

