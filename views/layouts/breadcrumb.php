<div class="content" style="min-height: auto;">
    <?= \yii\widgets\Breadcrumbs::widget([

        // Breadcrumb links
        'links'    => isset($this->params[ 'breadcrumbs' ]) ? $this->params[ 'breadcrumbs' ] : [ ],

        // The base class for the breadcrumb tag
        'options'  => [
            'class' => 'breadcrumb-line breadcrumb  breadcrumb-line-component'
        ],

        // Configure the home link
        'homeLink' => [
            'label' => 'Home',
            'template' => '<li><a href="'.Yii::$app->homeUrl.'"><i class="fa fa-home text-muted"></i> &nbsp; Home</a></li>',
            'encode'   => false,
        ],

    ]) ?>
</div>