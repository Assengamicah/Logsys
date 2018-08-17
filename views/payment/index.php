<?php

/* @var $this yii\web\View */
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
//use kartik\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Typeahead;

$this->title = 'Client Profiles';

?>



<div class="panel">
    <div class="panel-heading">
	 <?php if (Yii::$app->session->hasFlash('asuccess')){ ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('asuccess'); ?>
        </div>
	 <?php } ?>
        <h4 class="panel-title text-bold-500">
		 <?php $form = ActiveForm::begin(['enableClientValidation'=>false,
	 'enableAjaxValidation'=>false,
	 'fieldConfig' => [
                        'template' => "{input}",
                        'options' => [
                            'tag'=>false
                        ]
    ]
	 ]); ?>
		<table>
		<tr><td width='50%'><b>Cargo That Have Not Been Paid</b></td><td align='right' width='50%'>
					<?= $form->field($model, 'name')->textInput(['autofocus' => true,])->widget(Typeahead::classname(),[
							'options' => ['placeholder' => 'Search by Control # / Client Name / Mobile Number'],
							'scrollable' => true,
							'pluginOptions' => ['highlight'=>true],
							'dataset' => [
								[
								'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
								'display' => 'value',
								'remote' => [
								'url' => Url::to(['payment/stopay']) . '?q=%QUERY',
								'wildcard' => '%QUERY'
								]
								]
							]
							])->label(false); ?> 
					</td>
					<th width="10%" align="right"><b><button type="submit" name ="btnSName" class="btn primary btn-rounded pl-20 pr-20"><span class="text-regular small"> Search </span></button></b></th>
					</tr></table>
         <?php ActiveForm::end(); ?>
            <hr />
        </h4>
        
    
    <div class="table-responsive">

        <?php
					  	  
					   echo  GridView::widget(['dataProvider' => $dataProvider,
					    'emptyText' => '<b><i>There is no any Received Item For Payments Found..</i></b>',
						'layout' => '{items}{pager}',
						'columns' => [
						'Sn',
						'Control Number',
						'BL #',
						'Container#',
						[
								   'label' => 'Cargo',
								   'format' => 'raw',
								   'value' => function ($data) {
									             $cn = $data['Control Number'];
												 
												 $cnt = 1;
			$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$cn'")->queryScalar();
			if($ccn)
			{
				$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
			}
			$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$cn'")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= '<b>'.$rs[0].'</b>: Items : '.$rs[2].' : Descr : '.$rs[3].' , ';
						
					  }
					return rtrim($it,' , ');
											 },
						],
						'CBM',
						'Price USD',
						'Client',
						'Phone Number',
						
						
						[
								   'label' => 'Action',
								   'format' => 'raw',
								   'value' => function ($data) {
												 return Html::a('Process',['payment/prct', 'cid' => $data['cid']],['target'=>'_blank']);
											 },
						],
						],
					'tableOptions' =>['class' => 'table table-bordered table-gray'],
                       ]); 
					  
                    ?>

    </div>
	</div>
</div>




