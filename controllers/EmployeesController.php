<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use app\models\Employees;
use app\models\Roles;
use app\models\Itemgroup;
use app\models\Userrole;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\data\SqlDataProvider;
use app\models\Trace;
use app\models\Items;
use app\models\Sline;
use app\models\Rate;
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
	 
	 
	 
	 //////////////////////////////////////////////////////////////////////////////////
	//Manage Shipping Line
	
	public function actionSline() 
	{
        if(!FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = new Sline;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('slsuccess',"New Shipping Line Has Been Successful Added To The System.");
		  return $this->refresh();
        }
		
        return $this->render('_fsl',['model'=>$model,'dataProvider'=>$this->getSlines()]);
    }
	
	public function actionEsline($slid) 
	{
        if(!FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = $this->loadSline($slid);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('slsuccess',"Shipping Line Details Updated Successful.");
		  return $this->redirect(['employees/sline']);
        }
		
        return $this->render('_fsl',['model'=>$model,'dataProvider'=>$this->getSlines()]);
    }
	
	
	protected function loadSline($slid)
    {
        if (($model = Sline::findOne($slid)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Invalid Request.');
        }
    }
	
	public function getSlines()
	{
	      $q2 ="SELECT @s:=@s+1 as Sn,name as 'Name',paddress as 'Physical Address',address as 'Postal Address',";
		  $q2 .="phone as 'Phone',email as 'Email',slid FROM sline,(SELECT @s:=0) AS s ORDER BY name";
		  
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM sline")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Name'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);

        return $dataProvider;							
		
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////
	///Item Group
	
	public function actionItgroup() 
	{
        if(!FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = new Itemgroup;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('igsuccess',"Shipping Item Group Successful added to the System.");
		  return $this->refresh();
        }
		
        return $this->render('_fitemg',['model'=>$model,'tbG'=>$this->getGNames()]);
    }
	
	public function actionEitg($gid) 
	{
        if(!FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = $this->loadGItem($gid);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('iGsuccess',"Shipping Item Group Name Updated Successful.");
		  return $this->redirect(['employees/itgroup']);
        }
		
        return $this->render('_fitemg',['model'=>$model,'tbG'=>$this->getGNames()]);
    }
	
	
	protected function loadGItem($iid)
    {
        if (($model = Itemgroup::findOne($iid)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Invalid Request.');
        }
    }
	
	public function getGNames()
	{
	    $q ="SELECT name,cper,FORMAT(rate,2),gid FROM itemgroup ORDER BY name";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbG = '';
		if(!empty($rst))
		{ 
	        $i = 1;
			
			$tbG .="<table class='table table-bordered table-gray footable'><thead><tr><th><b>SN</b></th><th>GROUP ITEM NAME</th>";
		    $tbG .="<th>CHARGED PER</th><th>RATE - USD</th><th>ACTION</th></tr></thead>";
		     foreach($rst as $rs)
			  {
			     
				
				$tbG .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td><b>$rs[2]</b></td>";
				$tbG .="<td><b>".Html::a('Edit',['employees/eitg','gid'=>$rs[3]])."</b></td></tr>";
				$i++;
			  }
			   $tbG .="</table>";
		  }	
        return $tbG;		  
		
	}
	/////////////////////////////////////////
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////
	///REGISTER Items
	
	public function actionAdditem() 
	{
        if(!FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = new Items;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('isuccess',"Shipping Item Successful added to the System.");
		  return $this->refresh();
        }
		
        return $this->render('_fitems',['model'=>$model,'tbGI'=>$this->getItems()]);
    }
	
	public function actionEitem($iid) 
	{
        if(!FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = $this->loadItem($iid);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('isuccess',"Shipping Item Updated Successful.");
		  return $this->redirect(['employees/additem']);
        }
		
        return $this->render('_fitems',['model'=>$model,'tbGI'=>$this->getItems()]);
    }
	
	
	protected function loadItem($iid)
    {
        if (($model = Items::findOne($iid)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Invalid Request.');
        }
    }
	
	public function getItems()
	{
	    $q ="SELECT g.name,g.cper,g.gid,COUNT(i.gid) FROM items i INNER JOIN itemgroup g ON g.gid = i.gid ";
		$q .="GROUP BY g.name,g.cper,g.gid ORDER BY g.name";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbG = '';
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbG .="<table class='table table-bordered table-gray footable'><thead><tr><th><b>SN</b></th><th>GROUP NAME</th>";
		    $tbG .="<th>CHARGED PER</th><th>ITEM NAME</th><th>ACTION</th></tr></thead>";
		     foreach($rst as $rs)
			  {
			    $tbG .="<tr><td rowspan =$rs[3]>$i</td><td rowspan =$rs[3]>$rs[0]</td><td rowspan =$rs[3]>$rs[1]</td>";
				$r2 = Yii::$app->db->createCommand("SELECT name,iid FROM items WHERE gid ='$rs[2]' ORDER BY name")->queryAll(false);
				 foreach($r2 as $r)
				 {
				  $tbG .="<td>$r[0]</td><td><b>".Html::a('Edit',['employees/eitem','iid'=>$r[1]])."</b></td></tr>";
				  
				 }
				 $i++;
			  }
			   $tbG .="</table>";
		  }	
        return $tbG;		  
		
	}
	
	public function getItems_Old()
	{
	      $q2 ="SELECT @s:=@s+1 as Sn, i.name as 'Item Name',i.cper as 'Charged Per',FORMAT(i.rate,2) as 'Rate - USD',";
		  $q2 .="CONCAT(e.fname,' ',e.mname,' ',e.sname)as 'Registered By',i.iid FROM items i INNER JOIN employees e ";
		  $q2 .="On e.empid = i.cby,(SELECT @s:=0) AS s WHERE i.inext = 'E' ORDER BY i.name DESC";
		
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM items WHERE inext ='E'")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Item Name','Charged Per'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);

        return $dataProvider;							
		
	}
	///////////////////////////////////////////////////////////////////
	
	public function actionExrate()
	{
	  
	   $model = new Rate;
	  if ($model->load(Yii::$app->request->post()) && $model->validate()) 
	  {
			  $conn = Yii::$app->db;
		      $conn->createCommand("UPDATE exchangerate SET status='O' WHERE status='C'")->execute();
			  
			  $model->save();
			  $tzs = number_format($model->erate,2);
			  Yii::$app->session->setFlash('rate',"Exchange Rate Has Been Successful Set to 1USD = $tzs TSH.");
			  return $this->render('vwexrate');
		   
	    }
	   return $this->render('_fexrate',array(
			'model'=>$model,
		));
	   
	}

/////////////////////////////////////   Employees  //////////////////////////////////////////////////////////
    public function actionIndex()
    {
        //$dataProvider = new ActiveDataProvider(['query' => Employees::find()->where(['status' => A])]);
		
		$dataProvider = new SqlDataProvider([
		'sql' => 'SELECT e.empid AS empid,e.empcode AS empcode,e.fname AS fname,e.mname AS mname,e.sname AS sname,' .
		'(SELECT cname FROM countries WHERE cid = e.cid) AS country' . 
             ' FROM employees e WHERE STATUS = "A" ' ,
		]);

        return $this->render('index', ['dataProvider' => $dataProvider,]);
    }
	
	public function actionTstaff()
    {
        //$dataProvider = new ActiveDataProvider(['query' => Employees::find()->where(['status' => T])]);
		
		$dataProvider = new SqlDataProvider([
		'sql' => 'SELECT e.empid AS empid,e.empcode AS empcode,e.fname AS fname,e.mname AS mname,e.sname AS sname,' .
		'(SELECT cname FROM countries WHERE cid = e.cid) AS country' . 
             ' FROM employees e WHERE STATUS = "T" ORDER BY e.empid DESC ' ,
		]);

        return $this->render('tstaff', ['dataProvider' => $dataProvider,]);
    }

    public function actionViewstaff($id)
    {	
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
			
			$cby = Yii::$app->user->id;
			
			$query = "INSERT INTO userrole(rid,userid,fdate,tdate,status,cby,cdate) VALUES('$model->role','$model->empid','$model->fdate','$model->tdate','A','$cby',NOW())";
			Yii::$app->db->createCommand($query)->execute();
			
			Yii::$app->session->setFlash('ssuccess',"Staff Registered Successful.");
			
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
			Yii::$app->session->setFlash('usuccess',"Staff Registered Successful.");
			
            return $this->redirect(['viewstaff', 'id' => $model->empid]);
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
		
		Yii::$app->session->setFlash('ssuccess',"Staff Terminated Successful.");
			
		return $this->redirect(['index']);
	}
	
	public function actionReinstate($id)
	{
		$query = "UPDATE employees SET status = 'A' WHERE empid = '$id' ";
		Yii::$app->db->createCommand($query)->execute();
		
		Yii::$app->session->setFlash('ssuccess',"Staff Reinstated Successful.");
			
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
			//Yii::$app->session()->setFlash('success',"Role Registered Successfully");
			
			Yii::$app->session->setFlash('rsuccess',"Role Registered Successful.");
			
            return $this->refresh();
        }
		
		$dataProvider = new ActiveDataProvider(['query' => Roles::find()->orderBy(['rid' => SORT_ASC])]);

        return $this->render('role_form', ['model' => $model, 'dataProvider'=>$dataProvider]);
	}
	
	public function actionUpdate($id)
	{
		$model = $this->findRole($id);
		
		if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
			Yii::$app->session->setFlash('rsuccess',"Role Updated Successful.");
			
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
				Yii::$app->session->setFlash('sroleno',"Staff Already Posesses the selected role.");
				
				return $this->refresh();
			}
			else
			{
				$date = explode('/',$model->fdate);
				$model->fdate = $date[2].'-'.$date[1].'-'.$date[0];
				
				$date1 = explode('/',$model->tdate);
				$model->tdate = $date1[2].'-'.$date1[1].'-'.$date1[0];
				
				$cby = Yii::$app->user->id;
				
				$query1 = "INSERT INTO userrole(rid,userid,fdate,tdate,status,cby,cdate) VALUES('$model->rid','$model->userid','$model->fdate','$model->tdate','A','$cby',NOW())";
				Yii::$app->db->createCommand($query1)->execute();
				
				Yii::$app->session->setFlash('srolesuccess',"Role Assigned Successful.");
				
				return $this->refresh();
			}
        }
		
		$dataProvider = new SqlDataProvider([
		'sql' => 'SELECT id as id,r.name AS role,CONCAT(e.fname," ",IFNULL(e.mname,"")," ", e.sname) AS staff,DATE_FORMAT(u.fdate,"%d/%m/%Y") AS startdate,DATE_FORMAT(u.tdate,"%d/%m/%Y") AS enddate ' . 
             'FROM userrole u ' .
             'INNER JOIN roles r ON u.rid = r.rid ' .
             'INNER JOIN employees e ON u.userid = e.empid WHERE e.status = "A" AND u.status = "A" ORDER BY id ASC' ,
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
			//$query = "SELECT * From userrole WHERE userid = '$model->userid' AND rid = '$model->rid' AND status = 'A' ";
			//$rst = Yii::$app->db->createCommand($query)->queryAll(false);
			//if(!empty($rst))
			//{
				//Yii::$app->session->setFlash('sroleno',"Staff Already Posesses the selected role.");
				
				//return $this->redirect(['userrole']);
			//}
			//else
			//{
				$date = explode('/',$model->fdate);
				$model->fdate = $date[2].'-'.$date[1].'-'.$date[0];
				
				$date1 = explode('/',$model->tdate);
				$model->tdate = $date1[2].'-'.$date1[1].'-'.$date1[0];
				
				$query1 = "UPDATE userrole SET rid = '$model->rid', userid = '$model->userid', fdate = '$model->fdate', tdate = '$model->tdate' WHERE id = '$model->id' ";
				Yii::$app->db->createCommand($query1)->execute();
				
				//Yii::$app->session()->setFlash('susuccess', "Staffrole Updated Successfully");
				Yii::$app->session->setFlash('susuccess',"Staffrole Updated Successfully.");
				
				return $this->redirect(['userrole']);
			//}
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
		
		Yii::$app->session->setFlash('stsuccess',"Role Terminated Successful.");
				
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
