<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


?>
<br /><br />
<?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>

<div class="col-md-3 col-md-push-4 ml-18">

    <div class="row">
<div class="login-box-body">
    <div class="login-logo logo">
        <img src="<?= Yii::$app->request->baseUrl .'/img/biglogo.png' ?>" class="logo-img" width="100" height="100">
    </div>
    
       <fieldset>
                    <legend class="text-muted mb-20 small text-bold-500">
                        Please enter your credentials to log in
                    </legend> 
                    <?= $form->field($model, 'uname')->textInput(['placeholder' => "Username"])->label(false) ?>
					<?= $form->field($model, 'password')->passwordInput(['placeholder' => "Password"])->label(false);?>
                    <div class="form-group text-right">
                        <button type="submit" class="btn primary btn-block">Login &nbsp; <i class="icon-arrow-right13"></i>
                    </div>
                    
                </fieldset>

    </div>
    <br />
    <p class="text-center copyright text-small">Copyright &copy; 2017 <b> <a
                href="#"> FROSTAN </a> </b>
    </p>
	


 </div>

</div>
 <?php ActiveForm::end(); ?>
 

