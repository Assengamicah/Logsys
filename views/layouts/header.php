<?php 
use app\models\FrostanRoles;
?>
<header style="height: 60px;">

    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
			
            <div class="collapse navbar-collapse">
			 <ul class="nav navbar-nav">
                    <li>
                        <img src="<?= Yii::$app->request->baseUrl .'/img/socean.png' ?>" class="logo-img"  height="52" width="100%" style="border-radius: 3px;">
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
				 <?php if(FrostanRoles::isOperation()) { ?>
				 <li class="dropdown visible-lg">
                        <a href="<?= \yii\helpers\Url::to(['operation/index']) ?>" >
                            Cargo Handling
                            <b class="caret"></b>
                        </a>
                    </li>
				 <?php }  if(FrostanRoles::isSales()) {  ?>
					 <li class="dropdown visible-lg">
                       <a href="<?= \yii\helpers\Url::to(['clearing/index']) ?>">
                            Clearing
                            <b class="caret"></b>
                        </a>
                    </li>
					 <?php }  if(FrostanRoles::isManager()) {  ?>
					 <li class="dropdown visible-lg">
                        <a href="<?= \yii\helpers\Url::to(['payment/index']) ?>">
                            Payments
                            <b class="caret"></b>
                        </a>
                    </li>
					 <li class="dropdown visible-lg">
                       <a href="<?= \yii\helpers\Url::to(['dispatching/index']) ?>">
                            Dispatching
                           
                        </a>
                       
                    </li>
					 <?php }  if(FrostanRoles::isAdmin()) {  ?>
					 <li class="dropdown visible-lg">
                        <a href="<?= \yii\helpers\Url::to(['employees/index']) ?>">
                            Administration
                            <b class="caret"></b>
                        </a>
                    </li>
					 <?php }  ?>
					
                    <li class="dropdown visible-lg">
                        
						<a href="<?= \yii\helpers\Url::to(['site/logout']) ?>">
                           <?php echo Yii::$app->user->identity->fullname; ?> 
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu pt-10">
							<li><a href="<?= \yii\helpers\Url::to(['site/profile']) ?>">My Profile</a> </li>
                            <li class="divider"></li>
                            <li><a href="<?= \yii\helpers\Url::to(['site/logout']) ?>">Log out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

</header>