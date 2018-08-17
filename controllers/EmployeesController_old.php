<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use app\models\Employees;
use app\models\Roles;
use app\models\Userrole;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\data\SqlDataProvider;
use app\models\FrostanRoles;

/**
 * EmployeesController implements the CRUD actions for Employees model.
 */
class EmployeesController extends Controller
{
    /**
     * @inheritdoc
     */
	 
	public $menu = 'adminmenu';
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

/////////////////////////////////////   Employees  //////////////////////////////////////////////////////////
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(['query' => Employees::find()->where(['status' => A])]);

        return $this->render('index', ['dataProvider' => $dataProvider,]);
    }
	
	public function actionTstaff()
    {
        $dataProvider = new ActiveDataProvider(['query' => Employees::find()->where(['status' => T])]);

        return $this->render('tstaff', ['dataProvider' => $dataProvider,]);
    }

    public function actionViewstaff($id)
    {
		/*$dataProvider = new SqlDataProvider([
		'sql' => 'SELECT e.empcode AS code,CONCAT(e.fname,IFNULL(e.mname,""),e.sname)AS name,e.gender AS gender,e.email AS email,j.name AS title,e.telno AS telno,z.name AS zone,e.status AS status,e.cby AS cby,e.cdate AS cdate ' . 
             'FROM employees e INNER JOIN jobtitles j ON e.titleid = j.titleid INNER JOIN zones z ON e.zid = z.zid WHERE empid = "$id" ',
		]);*/
		
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate()
    {
        $model = new Employees(['scenario' => 'create']);

        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
			$date = explode('/',$model->fdate);
			$model->fdate = $date[2].'-'.$date[1].'-'.$date[0];
				
			$date1 = explode('/',$model->tdate);
			$model->tdate = $date1[2].'-'.$date1[1].'-'.$date1[0];
			
			$query = "INSERT INTO userrole(rid,userid,fdate,tdate) VALUES('$model->role','$model->empid','$model->fdate','$model->tdate')";
			Yii::$app->db->createCommand($query)->execute();
			
			Yii::$app->getSession()->setFlash('success', [
			'message' => Yii::t('app', Html::encode('Staff Registered Successfully')),
			]);
			
			
            //return $this->redirect(['view', 'id' => $model->empid]);
			return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model,]);
    }

    public function actionUpdatestaff($id)
    {
        $model = $this->findModel($id);
		
		$model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
			Yii::$app->getSession()->setFlash('success', [
			'message' => Yii::t('app', Html::encode('Staff Updated Successfully')),
			]);
			
            return $this->redirect(['viewstaff', 'id' => $model->empid]);
			//return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model,]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
	
	public function actionTerminatestaff($id)
	{
		$query = "UPDATE employees SET status = 'T' WHERE empid = '$id' ";
		Yii::$app->db->createCommand($query)->execute();
		
		Yii::$app->getSession()->setFlash('success', [
			'message' => Yii::t('app', Html::encode('Staff Terminated Successfully')),
			]);
			
		return $this->redirect(['index']);
	}
	
	public function actionReinstate($id)
	{
		$query = "UPDATE employees SET status = 'A' WHERE empid = '$id' ";
		Yii::$app->db->createCommand($query)->execute();
		
		Yii::$app->getSession()->setFlash('success', [
			'message' => Yii::t('app', Html::encode('Staff Reinstated Successfully')),
			]);
			
		return $this->redirect(['index']);
	}

    protected function findModel($id)
    {
        if (($model = Employees::findOne($id)) !== null) 
		{
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////  Roles    /////////////////////////////////////////////////////////////////////////////////////////
	
	public function actionRoles()
	{
		$model = new Roles;
		
		if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
			Yii::$app->getSession()->setFlash('success', [
			'message' => Yii::t('app', Html::encode('Role Registered Successfully')),
			]);
			
            return $this->refresh();
        }
		
		$dataProvider = new ActiveDataProvider(['query' => Roles::find()]);

        return $this->render('role_form', ['model' => $model, 'dataProvider'=>$dataProvider]);
	}
	
	public function actionUpdate($id)
	{
		$model = $this->findRole($id);
		
		if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
			Yii::$app->getSession()->setFlash('success', [
			'message' => Yii::t('app', Html::encode('Role Updated Successfully')),
			]);
			
            return $this->redirect(['roles']);
        }
		
		$dataProvider = new ActiveDataProvider(['query' => Roles::find()]);

        return $this->render('uprole_form', ['model' => $model, 'dataProvider'=>$dataProvider]);
	}
	
	public function actionView($id)
    {
		/*$dataProvider = new SqlDataProvider([
		'sql' => 'SELECT e.empcode AS code,CONCAT(e.fname,IFNULL(e.mname,""),e.sname)AS name,e.gender AS gender,e.email AS email,j.name AS title,e.telno AS telno,z.name AS zone,e.status AS status,e.cby AS cby,e.cdate AS cdate ' . 
             'FROM employees e INNER JOIN jobtitles j ON e.titleid = j.titleid INNER JOIN zones z ON e.zid = z.zid WHERE empid = "$id" ',
		]);*/
		
        return $this->render('viewrole', ['model' => $this->findRole($id)]);
    }
	
	protected function findRole($rid)
    {
        if (($model = Roles::findOne($rid)) !== null) 
		{
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////  User Roles   /////////////////////////////////////////////////////////////////////////////////////////////
	
	public function actionUserrole()
	{
		$model = new Userrole;
		
		if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
			$query = "SELECT * From userrole WHERE userid = '$model->userid' AND rid = '$model->rid' AND status = 'A' ";
			$rst = Yii::$app->db->createCommand($query)->queryAll(false);
			if(!empty($rst))
			{
				Yii::$app->getSession()->setFlash('error', [
				'message' => Yii::t('app', Html::encode('Selected staff already posseses the selected role')),
				]);
				
				return $this->refresh();
			}
			else
			{
				$date = explode('/',$model->fdate);
				$model->fdate = $date[2].'-'.$date[1].'-'.$date[0];
				
				$date1 = explode('/',$model->tdate);
				$model->tdate = $date1[2].'-'.$date1[1].'-'.$date1[0];
				
				$query1 = "INSERT INTO userrole(rid,userid,fdate,tdate,status,cdate) VALUES('$model->rid','$model->userid','$model->fdate','$model->tdate','A',NOW())";
				Yii::$app->db->createCommand($query1)->execute();
				
				Yii::$app->getSession()->setFlash('success', [
				'message' => Yii::t('app', Html::encode('Role Assigned Successfully')),
				]);
				
				return $this->refresh();
			}
        }
		
		$dataProvider = new SqlDataProvider([
		'sql' => 'SELECT id as id,r.name AS role,CONCAT(e.fname," ",IFNULL(e.mname,"")," ", e.sname) AS staff,DATE_FORMAT(u.fdate,"%d/%m/%Y") AS startdate,DATE_FORMAT(u.tdate,"%d/%m/%Y") AS enddate ' . 
             'FROM userrole u ' .
             'INNER JOIN roles r ON u.rid = r.rid ' .
             'INNER JOIN employees e ON u.userid = e.empid WHERE e.status = "A" AND u.status = "A" ' ,
		]);

        return $this->render('urole_form', ['model' => $model, 'dataProvider' => $dataProvider]);
	}
	
	public function actionVsrole($id)
	{
		$model = $this->loadUrole($id);

        return $this->render('vsrole', ['model' => $model]);
	}
	
	public function actionUpsrole($id)
	{
		$model = $this->loadUrole($id);
		$dt = explode('-',$model->fdate);
		$model->fdate = $dt[2].'/'.$dt[1].'/'.$dt[0];
		$dt = explode('-',$model->tdate);
		$model->tdate = $dt[2].'/'.$dt[1].'/'.$dt[0];
		
		if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
			$query = "SELECT * From userrole WHERE userid = '$model->userid' AND rid = '$model->rid' AND status = 'A' ";
			$rst = Yii::$app->db->createCommand($query)->queryAll(false);
			if(!empty($rst))
			{
				Yii::$app->getSession()->setFlash('error', [
				'message' => Yii::t('app', Html::encode('Selected staff already posseses the selected role')),
				]);
				
				return $this->redirect(['userrole']);
			}
			else
			{
				$date = explode('/',$model->fdate);
				$model->fdate = $date[2].'-'.$date[1].'-'.$date[0];
				
				$date1 = explode('/',$model->tdate);
				$model->tdate = $date1[2].'-'.$date1[1].'-'.$date1[0];
				
				$query1 = "UPDATE userrole SET rid = '$model->rid', userid = '$model->userid', fdate = '$model->fdate', tdate = '$model->tdate' ";
				Yii::$app->db->createCommand($query1)->execute();
				
				Yii::$app->getSession()->setFlash('success', [
				'message' => Yii::t('app', Html::encode('Staffrole Updated Successfully')),
				]);
				
				return $this->redirect(['userrole']);
			}
        }
		
		//$dataProvider = new ActiveDataProvider(['query' => Roles::find()]);
		
		$dataProvider = new SqlDataProvider([
		'sql' => 'SELECT id as id,r.name AS role,CONCAT(e.fname," ",IFNULL(e.mname,"")," ", e.sname) AS staff,DATE_FORMAT(u.fdate,"%d/%m/%Y") AS startdate,DATE_FORMAT(u.tdate,"%d/%m/%Y") AS enddate ' . 
             'FROM userrole u ' .
             'INNER JOIN roles r ON u.rid = r.rid ' .
             'INNER JOIN employees e ON u.userid = e.empid WHERE e.status = "A" AND u.status = "A" ' ,
		]);

        return $this->render('upsrole_form', ['model' => $model, 'dataProvider'=>$dataProvider]);
	}
	
	public function actionTsrole($id)
	{
		$query = "UPDATE userrole SET status = 'T' WHERE id = '$id' ";
		Yii::$app->db->createCommand($query)->execute();
		
		Yii::$app->getSession()->setFlash('success', [
				'message' => Yii::t('app', Html::encode('Staff terminated succesfully')),
				]);
				
		return $this->redirect(['userrole']);
	}
	
	protected function loadUrole($id)
    {
        if (($model = Userrole::findOne($id)) !== null) 
		{
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
