<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Userp;
use app\models\FrostanRoles;
use yii\helpers\Html;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
     public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['login,admlogin,index,logout'],
                'rules' => [
                    [
                        'actions' => ['logout','cpwd','admlogout'],
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        //return $this->render('index');
		if(Yii::$app->user->isGuest)
		{
		return $this->redirect(['login']);
		}
		else
		{
			if(FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['admin/index']);
		  }
		  elseif(FrostanRoles::isManager())
		  {
			  return $this->redirect(['manager/index']);
		  }
		  elseif(FrostanRoles::isQA())
		  {
			  return $this->redirect(['management/payments']);
		  }
		  elseif(FrostanRoles::isAccountant())
		  {
			  return $this->redirect(['management/payments']);
		  }
		  else
		  {
			  return $this->redirect(['operation/index']);
		  }
		}
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
         $this->layout = 'min-master';
		 
		$model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) 
		{
          if(FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['admin/index']);
		  }
		  elseif(FrostanRoles::isManager())
		  {
			  return $this->redirect(['manager/index']);
		  }
		  elseif(FrostanRoles::isQA())
		  {
			  return $this->redirect(['management/payments']);
		  }
		  elseif(FrostanRoles::isAccountant())
		  {
			  return $this->redirect(['management/payments']);
		  }
		  else
		  {
			  return $this->redirect(['operation/index']);
		  }
		  
        }

        return $this->render('login',['model'=>$model]);

    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(['site/login']);
    }

    public function actionProfile()
	{
        $this->layout = 'min-master';
		$id = Yii::$app->user->id;
		$q = "SELECT e.empcode,j.name,e.fname,e.mname,e.sname,e.email,e.telno FROM employees e ";
        $q .= "INNER JOIN jobtitles j ON j.titleid = e.titleid WHERE e.empid ='$id'";
		
		$rs = Yii::$app->db->createCommand($q)->queryOne(0);

		
			$tbTr ="<table class='table table-bordered table-gray'><thead>";
            $tbTr .="<tr><th>MY DETAILS</th><th>&nbsp;<b>".Html::a('Home',['site/index'])."</b></th></tr></thead><tbody>";
			$tbTr .="<tr><td width=30%>Employment ID:</td><td><b>$rs[0]</td></tr>";
			$tbTr .="<tr><td>Job Title:</td><td><b>$rs[1]</td></tr>";
			$tbTr .="<tr><td>First Name:</td><td><b>$rs[2]</td></tr>";
			$tbTr .="<tr><td>Middle Name:</td><td><b>$rs[3]</td></tr>";
			$tbTr .="<tr><td>Last Name:</td><td><b>$rs[4]</td></tr>";
			$tbTr .="<tr><td>Email:</td><td><b>$rs[5]</td></tr>";
            $tbTr .="<tr><td>Modile #:</td><td><b>$rs[6]</td></tr>";
			
            $tbTr .="<tr><td colspan='2'>".Html::a("<b>Edit</b>",['site/eprofile'])."</td></tr>"; 
            $tbTr .="</tbody></table>";            
							
		   
		return $this->render('profile',['tbData'=>$tbTr]);
		
	}
	
	public function actionEprofile()
    {
		$this->layout = 'min-master';
        if (Yii::$app->user->isGuest) 
		{
            return $this->redirect(['login']);
        }

        $model = $this->loadProfile(Yii::$app->user->id);
		$model->passwd = $model->pwd;
		$model->cpwd = $model->pwd;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
            Yii::$app->session->setFlash('usuccess',"User Account Updated Successful");
			return $this->redirect(['profile']);
        }
        return $this->render('eprofile', [
            'model' => $model,
        ]);
    }
	
	protected function loadProfile($rid)
    {
        if (($model = Userp::findOne($rid)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Invalid Request.');
        }
    }
	
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }



    public function actionCreate() {

        return $this->render('create');
    }
}
