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
        ['label' => '<i class="icon-cog"></i>Payments Main', 'url' => ['payment/index']],
		//['label' => '<i class="icon-calendar3"></i>View/Manage Addendum', 'url' => ['operation/addendum']],
		['label' => '____________________________', 'url' => ['operation/index#']],
		['label' => '<i class="icon-printer"></i>Re-Print Receipt', 'url' => ['payment/rerct']],
		['label' => '<i class="icon-printer4"></i>Re-Print Release Order', 'url' => ['payment/rerls']],
		['label' => '____________________________', 'url' => ['operation/index#']],
	
    ],
	'activeCssClass'=>'activeclass',
]);
?>
    </div>
</div>
