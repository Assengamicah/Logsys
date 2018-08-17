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
        ['label' => '<i class="icon-man-woman"></i>Manage Employees', 'url' => ['employees/index']],
		['label' => '<i class="icon-command"></i>User Roles Management', 'url' => ['employees/userrole']],
		['label' => '____________________________', 'url' => ['operation/index#']],
		['label' => '<i class="icon-tree7"></i>Roles Management', 'url' => ['employees/roles']],
		['label' => '____________________________', 'url' => ['operation/index#']],
		['label' => '<i class="icon-cash3"></i>New Exchange Rate', 'url' => ['employees/exrate']],
		['label' => '<i class="icon-unfold"></i>Add/Edit Shipping Items Group', 'url' => ['employees/itgroup']],
		['label' => '<i class="icon-clipboard2"></i>Add/Edit Item Details', 'url' => ['employees/additem']],
		['label' => '<i class="icon-ship"></i>Add/Edit Shipping Line', 'url' => ['employees/sline']],
		
    ],
	'activeCssClass'=>'activeclass',
]);
?>
    </div>
</div>
