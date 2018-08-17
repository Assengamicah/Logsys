<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

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
   
    <?= Html::cssFile(Yii::$app->request->baseUrl . '/css/dtree.css'); ?>
    <?= Html::jsFile(Yii::$app->request->baseUrl . '/css/dtree.js'); ?>
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

</body>
