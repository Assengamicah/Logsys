<?php 
use yii\widgets\Menu;

?>
<div class="sidebar">

    <div class="sidebar-wrapper">
        <div class="logo" title="APP NAME">
            <img src="<?= Yii::$app->request->baseUrl .'/img/biglogo.png' ?>" class="logo-img" width="45" height="45">
            <a href="#" class="simple-text">
               <b>DASHBOARD</b>
            </a>
        </div>

       <?php
echo Menu::widget(['options' => ['class' => 'nav'],'encodeLabels' => false,
    'items' => [

		['label' => '<i class="icon-cube3"></i>Job Tracking', 'url' => ['clearing/index']],
		['label' => '<i class="icon-cube4"></i>Register New Job', 'url' => ['clearing/newjob']],
				
		['label' => '____________________________', 'url' => ['clearing/index#']],
		
		['label' => '<i class="icon-user-check"></i>Create Invoice', 'url' => ['clearing/invhome']],
		//['label' => '<i class="icon-profile"></i>Cancel Invoice', 'url' => ['operation/clients']],
		['label' => '<i class="icon-profile"></i>Reprint Invoice', 'url' => ['clearing/reprintinv']],
		
		['label' => '____________________________', 'url' => ['clearing/index#']],
		
		['label' => '<i class="icon-user-check"></i>Register New ICD', 'url' => ['operation/nc']],
		['label' => '<i class="icon-profile"></i>Documents Type', 'url' => ['operation/clients']],
		['label' => '<i class="icon-profile"></i>Charges and Fees Type', 'url' => ['operation/clients']],
    ],
	'activeCssClass'=>'activeclass',
]);
?>
    </div>
</div>
