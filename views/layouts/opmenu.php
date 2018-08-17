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

		['label' => '<i class="icon-truck"></i>Loose Cargo Registration', 'url' => ['operation/neworder']],
		['label' => '<i class="icon-car2"></i>Package Cargo Receiving', 'url' => ['operation/newborder']],
		['label' => '<i class="icon-hammer-wrench"></i>Bale Processing', 'url' => ['operation/bnsorders']],
		['label' => '<i class="icon-stack"></i>Manage Cargo Yet To Be Shippped', 'url' => ['operation/nsorders']],
		['label' => '____________________________', 'url' => ['operation/index#']],
		['label' => '<i class="icon-grid6"></i>Full Container Loading', 'url' => ['operation/newfcl']],
		['label' => '<i class="icon-cube3"></i>Container Loading', 'url' => ['operation/clstep1']],
		['label' => '<i class="icon-cube4"></i>View/Update Container Cargo', 'url' => ['operation/eclstep1']],
		['label' => '<i class="icon-menu2"></i>Add Cargo Manifest', 'url' => ['operation/manifest']],
		['label' => '<i class="icon-ship"></i>View Shipped Items', 'url' => ['operation/cargos']],
				
		['label' => '____________________________', 'url' => ['operation/index#']],
		
		['label' => '<i class="icon-user-check"></i>Register New Client', 'url' => ['operation/nc']],
		['label' => '<i class="icon-profile"></i>View/Update Clients', 'url' => ['operation/clients']],
    ],
	'activeCssClass'=>'activeclass',
]);
?>
    </div>
</div>
