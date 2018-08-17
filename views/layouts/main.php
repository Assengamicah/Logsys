<?php

/* @var $this \yii\web\View */
/* @var $content string */
use app\models\FrostanRoles;
use yii\helpers\Html;
use app\assets\AppAsset;


AppAsset::register($this);

?>
<?= $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> | Application Name </title>
    <?= $this->head() ?>
</head>
<body class="sidebar-mini fixed">

<?= $this->beginBody() ?>

<div class="wrapper">

    <?php 
	echo $this->render($this->context->menu); 
	?>
    <div class="main-panel">

        <?= $this->render('header') ?>
        <!-- Content| Contains page content -->
        <div class="content">
            <div class="container-fluid">

                <?= $content ?>

            </div>

        </div>
        <!-- /content -->

        <?= $this->render('footer') ?>

        <!-- Main Footer -->
    </div>

</div>
<?= $this->endBody() ?>
</body>
</html>
<?php echo $this->endPage() ?>
