<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
//use yii\bootstrap\Tabs;
use kartik\tabs\TabsX;


?>

                    <?php echo TabsX::widget(['items' => [		          
            ['label' => '<i class="glyphicon glyphicon-user"></i> <b>1. Register/Update Client</b>', 'content' => $this->render('_fclient',['model'=>$model]),'active'=>$m1],
            ['label' => '<i class="glyphicon glyphicon-eye-open"></i> <b>2. View Client Details</b>', 'content' => $this->render('clientinfo',['cid'=>$cid]),'active'=>$m2],
								  
								  ],
								 'bordered'=>true,'encodeLabels'=>false]); ?>
                        
       
