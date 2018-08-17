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
        ['label' => '<i class="icon-check"></i>Product Dispatch', 'url' => ['sales/sale']],
		
    ],
	'activeCssClass'=>'activeclass',
]);
?>
    </div>
</div>
