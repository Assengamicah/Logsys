<?php

/* @var $this \yii\web\View */
/* @var $content string */

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
    <?= Html::cssFile(Yii::$app->request->baseUrl . '/css/bootstrap.min.css'); ?>
    <?= Html::cssFile(Yii::$app->request->baseUrl . '/css/main.css'); ?>

</head>
<body class="sidebar-mini fixed">

<?= $this->beginBody() ?>
<div class="wrapper">
    <div class="content">
        <div class="container-fluid">
           <?= $content ?>
        </div>
    </div>
</div>


<?= $this->endBody() ?>

<?= $this->render('script') ?>
</body>
