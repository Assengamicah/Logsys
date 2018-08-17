<?php

use yii\helpers\Html;
//use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use kartik\widgets\ActiveForm;
use kartik\widgets\Typeahead;
use yii\helpers\Url;


$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">

    <div class="panel">
        <div class="panel-heading">
           
            
       
		 <?php  $form = ActiveForm::begin([
            'id' => 'login2-form-horizontal', 
            'type' => ActiveForm::TYPE_HORIZONTAL,
			'enableClientValidation'=>false,
			'enableAjaxValidation'=>false,
            
        ]); 
      ?>     
        <table class="table table-gray" id="myid">
            <thead>
            <tr>
                
				<th width="44%"><font size='+1'><b>Clearing Module</b></font></th>
				<th align="right">
                   
	 
             <?= $form->field($model, 'jid',['addon' => ['append' => ['content' => Html::submitButton('Search', ['class'=>'btn primary','name'=>'btnSearch']), 'asButton' => true]]])->textInput(['autofocus' => true,'placeholder'=>'Search Job By Providing Job ID / BL Number / Container Number'])->widget(Typeahead::classname(),[
   
    'options' => ['placeholder' => 'Search Job By Providing Job ID / BL Number / Container Number'],
    'scrollable' => true,
    'pluginOptions' => ['highlight'=>true],
    'dataset' => [
        [
            'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
            'display' => 'value',
            'remote' => [
                'url' => Url::to(['clearing/joblist']) . '?q=%QUERY',
                'wildcard' => '%QUERY'
            ]
        ]
    ]
])->label(false); ?>
						 
                </th>
                
            </tr></thead>
           
           
        </table>
		<?php echo $tbJob; ?>
		 <?php ActiveForm::end(); ?>
		</div>
		<?php if(Yii::$app->session->hasFlash('error')){ ?>
            <div class="alert alert-error" role="alert">
                <?php echo Yii::$app->session->getFlash('error'); ?>
            </div>
        <?php } ?>
       <hr /> 
	   
  

        <?php
					  	  
					   echo  GridView::widget(['dataProvider' => $dataProvider,
					    'emptyText' => '<b><i>There is no any pending Job to be cleared at the Moment.</i></b>',
						'layout' => '{items}{pager}',
						'columns' => [
						'jobid',
						'client',
						'jobname',
						'tansno',
						'jobstatus',
						'regdate',
						'regby',
						[
								   'label' => 'Action',
								   'format' => 'raw',
								   'value' => function ($data) {
									      
												 return Html::a('Proceed',['clearing/doview', 'jid' => $data['jobid']]);
										   
											 },
						],
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); 
					  
                    ?>
  
	</div>
	
</div>