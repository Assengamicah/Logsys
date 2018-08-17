<div class="sidebar">

    <div class="sidebar-wrapper">
        <div class="logo" title="APP NAME">
            <img src="<?= Yii::$app->request->baseUrl .'/img/biglogo.png' ?>" class="logo-img" width="45" height="45">
            <a href="#" class="simple-text">
               <b>DASHBOARD</b>
            </a>
        </div>

        <ul class="nav">
            <li>
            <a href="<?php echo Yii::$app->homeUrl ?>">
                <i class="icon-meter2"></i>
                <span>Dashboard</span>
            </a>
            </li>
            <li>
                <a href="<?php echo Yii::$app->urlManager->createUrl('site/create') ?>">
                    <i class="icon-list"></i>
                    <span>Form Example</span>
                </a>
            </li>
            <li>
                <a href="<?php echo Yii::$app->urlManager->createUrl('site/login') ?>">
                    <i class="icon-user"></i>
                    <span>Login Page</span>
                </a>
            </li>
        </ul>
    </div>
</div>
