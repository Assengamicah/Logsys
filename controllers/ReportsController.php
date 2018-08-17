<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\data\SqlDataProvider;
use yii\filters\VerbFilter;
use app\models\FrostanRoles;
use yii\helpers\Html;
use yii\helpers\Json;

class ReportsController extends Controller
{
	public $menu = 'reportsmenu';
    public function behaviors()
     {
			return [
				'access' => [
					'class' => AccessControl::className(),
					'rules' => [
						[
							'allow' => true,
							'roles' => ['@'],
						],

						
					],
				],
			];
     }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
	{
		$this->layout = 'min-master2';
		if(FrostanRoles::isManager())
		{
			$masterSql="SELECT id,label,master,url FROM reportsmenu WHERE module='ICD'";
		}
		elseif(FrostanRoles::isOperation())
		{
			$masterSql="SELECT id,label,master,url FROM reportsmenu WHERE module='ICD' AND visibility IN('ROOT','INV','ALL')";  
		}
		elseif(FrostanRoles::isAdmin())
		{
			$masterSql="SELECT id,label,master,url FROM reportsmenu WHERE module='ICD' AND visibility IN('ROOT','REG','ADM')";  
		}
		elseif(FrostanRoles::isSales())
		{
			$masterSql="SELECT id,label,master,url FROM reportsmenu WHERE module='ICD' AND visibility IN('ROOT','INV','FIN')";  
		}
		else
		{
			$masterSql="SELECT id,label,master,url FROM reportsmenu WHERE module='ICD' AND visibility IN('ROOT','REG', 'DISP')";  
		}
	  
	   $data = Yii::$app->db->createCommand($masterSql)->queryAll(false);
	
		$trees='';
		foreach($data as $ds) {
			$id=$ds[0];
			$label=$ds[1];
			$master=$ds[2]; 
			
		    $url= $ds[3]."?rid=$id";
			$label=str_replace("'","",$label);
			
			if($master=='') {
				$master=0;
			}
			
			$tree="d.add($id,$master,'$label','$url');";
			$trees=$trees.' '.$tree;
		}
		
	  return $this->render('index',['trees'=>$trees]);
	}
	


}
