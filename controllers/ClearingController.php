<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\data\SqlDataProvider;
use yii\filters\VerbFilter;
use app\models\Clients;
use app\models\Orders;
use app\models\COrders;
use app\models\Orderitems;
use app\models\Squeeze;
use app\models\Squeeze2;
use app\models\Squeeze3;
use app\models\Manifest;
use app\models\RO;
use app\models\Shipping;
use app\models\Uorder;
use app\models\Rorders;
use app\models\Supcontacts;
use app\models\Shipping2;
use app\models\Shipping3;
use app\models\Jobs;
use app\models\CF;
use app\models\Inv;
use app\models\Invoiceitem;
use app\models\Jobsrch;
use app\models\Containers;
use app\models\Jobdocuments;
use app\models\LogisticsRoles;
use yii\web\UploadedFile;
use yii\helpers\Html;
use yii\helpers\Json;
use kartik\mpdf\Pdf;


class ClearingController extends Controller
{
  	public $menu = 'clearingmenu';
    /**
     * @inheritdoc
     */
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

    /**
     * Displays homepage.
     *
     * @return string
     */
	 
	
	
    public function actionIndex()
    {
        $model = new Jobsrch;
		$tbJob = '';
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		  if(isset($_POST['btnSearch'])) 
		  {
            if($model->load(Yii::$app->request->post()) && $model->validate())
			{
			$emp = explode(':',$model->jid);
			$qe ="SELECT j.jid,j.client,j.name,DATE_FORMAT(j.cdate,'%d/%m/%Y'),js.name,j.stid FROM jobs j INNER JOIN jobstage js ";
		    $qe .="ON js.stid = j.stid WHERE j.jid =:nid ";
            $rs = Yii::$app->db->createCommand($qe)->bindParam(':nid',$emp[0])->queryOne(0);
            if($rs) 
			{
               $tbJob = "<table class='table table-bordered table-gray'><thead><tr><th>JON ID</th><th>CLIENT</th><th>JOB NAME</th>";
		       $tbJob .= "<th>REG.DATE</th><th>JOB STAGE</th><th>ACTION</th></tr></thead>";

			   $tbJob .="<tr><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]</td>";
			   if($rs[5] < 8)
			   {
			     $tbJob .= "<td><b>".Html::a('Proceed',['clearing/doview','jid'=>$rs[0]])."</b></td></tr>";
			   }
			   else
			   {
				  $tbJob .= "<td>&nbsp</td></tr>";  
			   }
			 $tbJob .="</table>";
            } 
			else 
			{
				Yii::$app->session->setFlash('error', 'The Job Provided to Search Does not Exist.');
			}
			}
        }
      
		return $this->render('index',['model'=>$model,'tbJob'=>$tbJob,'dataProvider'=>$this->getJobList()]);
    }
	
	 public function actionJoblist($q = null) 
	 {
		$q = strtolower($q);	
		
		   $qd ="SELECT j.jid,ji.blno,ji.inumber FROM jobs j INNER JOIN jobstage js ";
		   $qd .="ON js.stid = j.stid INNER JOIN jobitems ji ON j.jid = ji.jid WHERE j.jid LIKE '$q%' UNION ";
		   
		   $qd .="SELECT j.jid,ji.blno,ji.inumber FROM jobs j INNER JOIN jobstage js ";
		   $qd .="ON js.stid = j.stid INNER JOIN jobitems ji ON j.jid = ji.jid WHERE ji.blno LIKE '$q%' UNION ";
		 
		   $qd .="SELECT j.jid,ji.blno,ji.inumber FROM jobs j INNER JOIN jobstage js ";
		   $qd .="ON js.stid = j.stid INNER JOIN jobitems ji ON j.jid = ji.jid WHERE LOWER(ji.inumber) LIKE '$q%' ";
		
			
			$data = Yii::$app->db->createCommand($qd)->queryAll(false);
			$out = [];
			foreach ($data as $d) {
				$out[] = ['value' => $d[0].':'.$d[1].':'.$d[2]];
			}
			echo Json::encode($out);
    }
	
	public function getJobList()
	 {
	   $q ="SELECT j.jid jobid,j.client,j.name jobname,DATE_FORMAT(j.cdate,'%d/%m/%Y') regdate,js.name jobstatus,j.tansno,";
	   $q .="CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname) as regby,cf.cname FROM jobs j INNER JOIN ";
	   $q .="jobstage js ON js.stid = j.stid INNER JOIN employees e ON e.empid = j.cby ";
	   $q .="INNER JOIN companyinfo cf ON cf.cid = j.cfid WHERE j.stid < 8";
	   
	   $cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM jobs WHERE stid < 8 ")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['jobname', 'rdate','regby'],],
							'pagination'=>[
							               'pageSize'=>4,
	                                      ],
						    ]);	
		
			return  $dataProvider;
	 }

	function getCode() 
	{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 3; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


public function actionInvhome()
	{
		unset($_SESSION['JobItems']);
	    $_SESSION['jid'] =  0;
	    $_SESSION['WA'] = 0;
		
		$model = new Jobsrch;
		$conn = Yii::$app->db;

		
		//////////////////////////
		$tbStd ="";
		 if(isset($_POST['btnSearch'])) 
		  {
            if($model->load(Yii::$app->request->post()) && $model->validate())
			{
			$emp = explode(':',$model->jid);
			$qe ="SELECT j.jid,j.client,j.name,DATE_FORMAT(j.cdate,'%d/%m/%Y'),js.name,j.stid FROM jobs j INNER JOIN jobstage js ";
		    $qe .="ON js.stid = j.stid WHERE j.jid =:nid ";
            $rs = Yii::$app->db->createCommand($qe)->bindParam(':nid',$emp[0])->queryOne(0);
            if($rs) 
			{ 
                        $tbStd .= "<table class='table table-bordered table-gray'>";
						$tbStd .= "<tr bgcolor='#0B67CD'><td><font color='#FFFFFF'><b>SN</b></font></td><td><font color='#FFFFFF'>";
						$tbStd .= "<b>CUSTOMER</b></font></td><td><font color='#FFFFFF'><b>JOB ID</b></font></td><td>";
						$tbStd .= "<font color='#FFFFFF'><b>JOBNAME</b></font></td>";
						$tbStd .= "<td><font color='#FFFFFF'><b>RECEIVED DATE</b></font></td><td><font color='#FFFFFF'><b>JOB STATUS</b></font></td></tr>";

			   $tbStd .="<tr><td>1.</td><td>$rs[1]</td><td>$rs[0]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]</td></tr>";
			   
			 $tbStd .="</table>";
            } 
			else 
			{
				Yii::$app->session->setFlash('error', 'The Job Provided to Search Does not Exist.');
			}
			}
        }
		
		
		 $q ="SELECT DISTINCT j.jid,j.client,j.name,DATE_FORMAT(j.cdate,'%d/%m/%Y'),cf.cname FROM jobs j  INNER JOIN companyinfo cf ";
		 $q .="ON cf.cid = j.cfid INNER JOIN jobitems ji ON j.jid = ji.jid ";
		 $q .="WHERE NOT ji.id IN (SELECT iid FROM invoiceitem WHERE iid IS NOT NULL) AND j.stid = 8 ORDER BY j.cdate ";
		 
		 $rst = $conn->createCommand($q)->queryAll(false);
		 $tbInv ="";
		  if(!empty($rst))
		   {
	                    $i = 1;
	                    $tbInv .="<table class='table table-bordered table-gray'>";
	                    $tbInv .= "<tr bgcolor='#ABE1FE'><td><b>SN</b></td><td><b>CUSTOMER</b></td><td><b>JOB ID</b></td><td><b>JOBNAME</b></td>";
						$tbInv .= "<td><b>C&F COMPANY</b></td><td><b>RECEIVED DATE</b></td><td><b>ACTION</b></td></tr>";
						 foreach($rst as $rs)
			              {
			               $tbInv .= "<tr><td>$i</td><td>$rs[1]</td><td><b>$rs[0]</b></td><td>$rs[2]</td><td>$rs[4]</td><td>$rs[3]</td>";
				 $tbInv .= "<td>".Html::a('Create Invoice',array('clearing/inv1','jid'=>$rs[0]),array('target'=>'_blank'))."</td></tr>";
						   $i++;
			              }
		     }
			else
			 {
				$tbInv .="<table border=1 cellpadding=1 cellspacing=0 width=100% >";
				$tbInv .="<tr><td><b><div class='alert alert-warning' role='alert'>No Job is available for you to create an Invoice at this time.</div></b></td></tr>";
			 }
		 $tbInv .= "</table>";
		 
		return $this->render('vwinv',['model'=>$model,'tbJob'=>$tbStd,'tbInv'=>$tbInv]);
	}
	
	
	public function getJOne($jid)
	{
		$conn = Yii::$app->db;
		
	    $q = "SELECT j.client,ji.blno,ji.bltype,j.name,j.quantity,s.name,ic.name,js.name,cf.cname FROM jobs j INNER JOIN jobitems ji ";
	    $q .="ON j.jid = ji.jid INNER JOIN sline s ON s.slid = ji.slid INNER JOIN icds ic ON ic.icd = j.icd INNER JOIN jobstage js ON ";
		$q .="js.stid = j.stid INNER JOIN companyinfo cf ON cf.cid = j.cfid WHERE j.jid = '$jid' LIMIT 1";
		$rs = $conn->createcommand($q)->queryOne(false);
		if($rs)
		{
		   $doc = $conn->createcommand("SELECT docattach FROM jobdocuments WHERE jid ='$jid' AND docid =1")->queryScalar();
		   $tbTr ="<table class='table table-bordered' >";
		   $tbTr .="<tr><td colspan=4 bgcolor='#0B67CD'><font color='#FFFFFF'><b>JOB DESCRIPTION</b></font></td></tr>";
		   $tbTr .="<tr><td>PART ONE:</td><td colspan=2>THIS JOB IS BEING HANDLED BY:&nbsp;<b>$rs[8]</b></td>";
		   $tbTr .="<td><b>Job Stage :&nbsp;<font color=blue>$rs[7]</font></b></td></tr>";
		   $tbTr .="<tr><td width=16%><b>&nbsp;Job ID:</b></td><td width=35%><b>$jid</b></td>";
		   $tbTr .="<td width=20%><b>&nbsp;Customer:</b></td><td>$rs[0]</td></tr>";
		   $tbTr .="<tr><td><b>&nbsp;BL #:</b></td><td>$rs[1]</td>";
		   $tbTr .="<td><b>&nbsp;Type Of Operation:</b></td><td>$rs[2]</td></tr>";
		   $tbTr .="<tr><td><b>&nbsp;BL Items:</b></td><td>$rs[3]</td>";
		   $tbTr .="<td><b>&nbsp;Quantity:</b></td><td>$rs[4]</td></tr>";
		   $tbTr .="<tr><td><b>&nbsp;Shipping Line:</b></td><td>$rs[5]</td>";
		   $tbTr .="<td><b>&nbsp;ICD:</b></td><td>$rs[6]</td></tr>";
		   //$tbTr .="<tr><td colspan=4><b>&nbsp;</td></tr>";
		  $q2 = "SELECT d.name,jd.docattach FROM jobdocuments jd INNER JOIN documents d ON d.docid = jd.docid WHERE jd.jid ='$jid' AND jd.docid IN(2,3,4,5)";
			$docs = $conn->createcommand($q2)->queryAll(false);
			foreach($docs as $d)
			{
				 $tbTr .="<tr><td colspan=2 align=right><b>$d[0]:&nbsp;</b></td>";
				 $tbTr .="<td colspan=2>&nbsp;<b>".Html::a('View',Yii::$app->request->baseUrl.'/cfdocs/'.$d[1],['target'=>'_blank'])."</b></td></tr>";

			}
		    //Other relevant documents
           $q3 = "SELECT d.name,jd.docattach,jd.id,jd.hascharges,jd.charges,jd.paidby,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),  ";
		   $q3 .= "DATE_FORMAT(jd.cdate,'%d/%m/%Y'),jd.inusd,jd.erate,jd.paidin,jd.hasvat,jd.vatamt,jd.vatinusd FROM jobdocuments jd  ";
		   $q3 .="INNER JOIN documents d ON d.docid = jd.docid INNER JOIN employees e ON e.empid = jd.cby WHERE jd.jid ='$jid' AND d.docid > 11";
		   $ord = $conn->createcommand($q3)->queryAll(false);
		    if(!empty($ord))
		    { 
			   $tbTr .="<tr><td colspan=4 bgcolor='#0B67CD'><font color='#FFFFFF'><b>EXPORT CERTIFICATE AND RELATED PERMITS</b></font></td></tr>";
			  
		      foreach($ord as $rd)
			  {
			    $tbTr .="<tr><td colspan=2>&nbsp;<b>".Html::a($rd[0],Yii::$app->request->baseUrl.'/cfdocs/'.$rd[1],['target'=>'_blank']).'&nbsp;|&nbsp;';
				$tbTr .= Html::a('<b>Remove</b>',['clearing/rmord','did'=>$rd[2],'jid'=>$jid,'type'=>'CERT'],['data'=>['confirm'=>'Are you sure you want to remove the selected Certificate?','method' => 'post',],])."&nbsp;&nbsp;|&nbsp;&nbsp;Uploaded by:&nbsp;</b>$rd[6]<br />&nbsp;<b>Date Uploaded:&nbsp;</b>$rd[7]</td>";
				if($rd[3] == 'Y')
				{
				  $det = "";
				  if($rd[11] == 'Y')
				  {
	$det = "&nbsp;VAT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(TZS):&nbsp;&nbsp;<b>".number_format($rd[12],2)."</b>&nbsp;In USD:&nbsp;<b>".number_format($rd[13],2)."</b><br />";  
				  }
				  $tbTr .= "<td colspan=2>&nbsp;Amount(TZS)&nbsp;:&nbsp;<b>".number_format($rd[4],2)."</b>&nbsp;In USD:&nbsp;<b>".number_format($rd[8],2)."</b>&nbsp;&nbsp;Exchange Rate:&nbsp;<b>".number_format($rd[9],2)."</b><br />$det&nbsp;Paid In:&nbsp;<b>$rd[10]</b>&nbsp;&nbsp;&nbsp;And Was Paid By:&nbsp;<b>$rd[5]</b></td>";
				}
				else
				{
					 $tbTr .= "<td><b>&nbsp;NO CHARGES</b></td><td>&nbsp;Paid By:&nbsp;<b>NA</b></td>";
				}
				$tbTr .="</tr>";  
			  } 
			  
		   }
		   
		   $qjid = "SELECT j.inumber,j.size,j.descr,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname) FROM jobitems j INNER JOIN ";
		   $qjid .= "employees e ON e.empid = j.cby WHERE j.jid ='$jid'";
		   $rst  = $conn->createCommand($qjid)->queryAll(false);
		   
		$tbTr .="<tr bgcolor='#B34C00'><td colspan=2><font color='#FFFFFF'><b>Job Registered Details</b></font></td>";
		   $tbTr .="<td colspan=2><font color='#FFFFFF'><b>Packed List</b></font></td></tr>";
		   //$tbTr .="<td><font color='#FFFFFF'><b>Registered By</b></font></td></tr>";
		   $j = 1;
		   foreach($rst as $r)
		   {
			    $tbTr .="<tr><td colspan=2><b>$j ]</b>&nbsp;$rs[3] #: <b><i>$r[0] - $r[1]</i></b></td><td colspan=2>$r[2]</td></tr>";
				$j++;
		   }
		   
		    $q4 = "SELECT d.name,jd.docattach,jd.hascharges,jd.charges,jd.paidby,jd.id,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),  ";
		    $q4 .= "DATE_FORMAT(jd.cdate,'%d/%m/%Y'),jd.inusd,jd.erate,jd.paidin,jd.hasvat,jd.vatamt,jd.vatinusd FROM jobdocuments jd INNER JOIN documents d ON ";
		    $q4 .="d.docid = jd.docid INNER JOIN employees e ON e.empid = jd.cby WHERE jd.jid ='$jid' AND d.docid IN(6,7,8,9,10,11)";
		   $pcert = $conn->createcommand($q4)->queryAll(false);
		    if(!empty($pcert))
		    { 
			   $tbTr .="<tr><td colspan=4 bgcolor='#0B67CD'><font color='#FFFFFF'><b>AVAILABLE JOB CLEARING DOCUMENTS</b></font></td></tr>";
		      foreach($pcert as $cert)
			  {
			    $tbTr .= "<tr><td colspan=2><b>".Html::a($cert[0],Yii::$app->request->baseUrl.'/cfdocs/'.$cert[1],['target'=>'_blank']).'&nbsp;&nbsp;';
			$tbTr .= "</b>&nbsp;&nbsp;|&nbsp;&nbsp;<b>Uploaded by:&nbsp;</b>$cert[6]<br />&nbsp;<b>Date Uploaded:&nbsp;</b>$cert[7]</td>";
				if($cert[2] == 'Y')
				{
				  //$tbTr .= "<td>&nbsp;Amount:&nbsp;<b>".number_format($cert[3],2)."</b></td><td>&nbsp;Paid By:&nbsp;<b>$cert[4]</b></td>";
				  
				  $det2 = "";
				  if($cert[11] == 'Y')
				  {
	$det2 = "&nbsp;VAT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(TZS):&nbsp;&nbsp;<b>".number_format($cert[12],2)."</b>&nbsp;In USD:&nbsp;<b>".number_format($cert[13],2)."</b><br />";  
				  }
				  
				   $tbTr .= "<td colspan=2>&nbsp;Amount(TZS):&nbsp;<b>".number_format($cert[3],2)."</b>&nbsp;In USD:&nbsp;<b>".number_format($cert[8],2)."</b>&nbsp;&nbsp;&nbsp;Exchange Rate:&nbsp;<b>".number_format($cert[9],2)."</b><br />$det2&nbsp;Paid In:&nbsp;<b>$rd[10]</b>&nbsp;&nbsp;&nbsp;And Was Paid By:&nbsp;<b>$cert[4]</b></td>";
				}
				else
				{
					 $tbTr .= "<td><b>NO CHARGES</b></td><td>&nbsp;Paid By:&nbsp;<b>NA</b></td>";
				}
				$tbTr .="</tr>";  
			  } 
			  
		   }
		   
		   
		   


		  $tbTr .="</table>";
		}
		else
		{
			 $tbTr ="<table class='table table-bordered' >";
			  $tbTr .="<tr><td colspan=4 bgcolor='#7d98c2'><font color='#FFFFFF'><b>Invalid Request</b></font></td></tr></table>";
		}
		return $tbTr;
	}
	////////////////////////////////////////////////////////////////////////////////////////////
	//Invoice Creation Logics and stuff like this
	
	public function actionInv1($jid)
	{
	
	    unset($_SESSION['JobItems']);
		unset($_SESSION['JobFees']);
		$_SESSION['ve'] = 0;
		$_SESSION['jid'] = $jid;
	  
		return $this->redirect(['psinvoice']);  
	}
	
 	
  public function actionPsinvoice()
	{
	   $jid = $_SESSION['jid'];	
	   $t2 = 0;
	   $t3 = 0;
	   $vt = $vu = 0;
	   $t2Usd = 0;
	   $t3Usd = 0;
	   $erate = Yii::$app->user->identity->rate;
	   $conn = Yii::$app->db;
	    $hasItems = false;
		
		$model=new Invoiceitem();
		$model2 = new CF();
		  
		$qi ="SELECT j.name,j.blno,j.quantity,j.client FROM jobs j WHERE j.jid ='$jid'";
		$item = $conn->createCommand($qi)->queryOne(false);
		
		if($_POST['Invoiceitem']['rdvat'] == 1)
		{
		   $_SESSION['ve'] = 1;
		}
		else
		{
		  $_SESSION['ve'] = 0;
		}
		$model->rdvat = $_SESSION['ve'];
        
		 
		if(isset($_POST['btnAdd2']))
		{
		    if(isset($_POST['CF']))
		     {
			   //$model2->attributes=$_POST['CF'];
			   $model2->load(Yii::$app->request->post());
			   if($model2->validate())
			   {
		            //$conn->createCommand("UPDATE jobitems SET amt ='$model->amount' WHERE id ='$model->iid'")->execute();
					if($model2->paidin2 == 'USD')
			         {
				        $inusd2 = $model2->amt;
				        $model2->amt = $inusd2 * $erate;
			         }
			        else
			        {
				      $inusd2 = $model2->amt / $erate;
			        }
					$_SESSION['JobFees'][$model2->fid.':'.$model2->amt.':'.$inusd2.':'.$model2->hasvat]++;  //add this chassis number to the session variable  
					
					$model2->paidin2 ='';
					$model2->amt ='';
			   } 
		     }
		 }
		 
		if(isset($_POST['btnAdd']))
		{
			   if($model->load(Yii::$app->request->post()) && $model->validate())
			   {
                				
				$_SESSION['JobItems'][$model->iid]++;  //add this chassis number to the session variable  
			   }
		 }
		 
		 $i = 1;
				 if($_SESSION['JobItems'])
				 {
				  $hasItems = true; 
				  $totalDoc = 0;
				  $totalDocUsd = 0;
				  $Total = 0;
				  $TotalUsd = 0;
				  $tOne = 0;
				  $tOneUsd = 0;
				  $tbInvoice ="<table class='table table-bordered table-gray'>";
		          $tbInvoice .="<tr bgcolor='#ABE1FE'><td><b>Remove</b></td><td><b>Description</b></td>";
		          $tbInvoice .="<td><b>Charges</b></td><td><b>Amount [TSH]</b></td><td><b>Amount [USD]</b></td></tr>";
				  
				  $qt = "SELECT d.name,jd.charges,jd.inusd,jd.hasvat,jd.vatamt,jd.vatinusd FROM jobdocuments jd INNER JOIN documents d ON d.docid = jd.docid ";
		          $qt .= "WHERE jd.jid ='$jid' AND jd.paidby !='$item[3]' AND jd.paid='N' and jd.hascharges ='Y' AND jd.invoicenum IS NULL ";
				  
				  $dp = $conn->createCommand($qt)->queryAll(false);
				  $bl = $conn->createCommand("SELECT blno FROM jobitems WHERE jid = '$jid' LIMIT 1")->queryScalar();
				  if(!empty($dp))
				  {
					$k = 1;
					  
				    foreach($dp as $p)
				    {
						
				     $pt = $p[1];
					 $pu = $p[2];
					 if($p[3] == 'Y')
					 {
						$pt = $p[1] - $p[4];
					    $pu = $p[2] - $p[5];
						
						$vt = $vt + $p[4];
						$vu = $vu + $p[5];  
					 }
					 $tbInvoice .="<tr><td>&nbsp;</td><td> $item[0] in BL #:&nbsp;<b>$bl</b></td>";
					 $tbInvoice .="<td>$p[0]</td><td>".number_format($pt,2)."</td><td>".number_format($pu,2)."</td></tr>";
					 $totalDoc = $totalDoc + $pt; 
					 $Total = $Total + $pt; 
					 $totalDocUsd = $totalDocUsd + $pu; 
					 $TotalUsd = $TotalUsd + $pu; 
				    }
				$tbInvoice .="<tr><td colspan=3 align=right><b>Sub Total</b></td><td><b>".number_format($totalDoc,2)."</b></td>";
				$tbInvoice .="<td><b>".number_format($totalDocUsd,2)."</b></td></tr>";
				  }
				  
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $qpi ="SELECT CONCAT('Container #: ',inumber,' - ',size,', Items:',descr),'Agency Fee',amt,inusd FROM jobitems WHERE id ='$ckey'";
				      $rowI = $conn->createCommand($qpi)->queryOne(false);
					 
				     $tbInvoice .="<tr><td><b>".Html::a('Remove',['clearing/rmcno','cno'=>$ckey])."</b></td>";
					  $tbInvoice .="<td colspan=2>$rowI[0]</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
				  }
				  
				  
				   if($_SESSION['JobFees'])
				   {
				     foreach($_SESSION['JobFees'] as $ckey=>$val)
		             {
					   $ck = explode(":",$ckey);
					   $qc ="SELECT name,hasvat FROM fees WHERE feeid ='$ck[0]'";
				       $rs = $conn->createCommand($qc)->queryOne(false);
					   
					    $t2 = $t2 + $ck[1];
					    $Total = $Total + $ck[1];
						
						$t2Usd = $t2Usd + $ck[2];
					    $TotalUsd = $TotalUsd + $ck[2];
						
						if($ck[3] == 'Y')
						{
							 $t3 = $t3 + $ck[1];
							 $t3Usd = $t3Usd + $ck[2];
						}
				        $tbInvoice .="<tr><td colspan=2 align=right><b>".Html::a('Remove',['clearing/rmcno2','cno'=>$ckey])."</b></td>";
					    $tbInvoice .="<td align=right>$rs[0]</td><td>".number_format($ck[1],2)."</td><td>".number_format($ck[2],2)."</td></tr>";
				    }
					
				  }
				  
				  ////////////////////////////////////////////////////////
				  $VAT = ($tOne * 0.18) + ($t3 * 0.18) + $vt;
				  $VAT2 = ($tOneUsd * 0.18) + ($t3Usd * 0.18) + $vu;
				  $BigTotal = $VAT + $Total;
				  $BigTotal2 = $VAT2 + $TotalUsd;
				  if($t2 != 0)
				  {
				   $tbInvoice .="<tr><td colspan=3 align=right><b>Sub Total</b></td><td><b>".number_format($t2,2)."</b></td>";
				   $tbInvoice .="<td><b>".number_format($t2Usd,2)."</b></td></tr>";
				  }
				  $tbInvoice .="<tr><td colspan=3 align=right><b>VAT - 18%</b></td><td><b>".number_format($VAT,2)."</b></td>";
				  $tbInvoice .="<td><b>".number_format($VAT2,2)."</b></td></tr>";
				  $tbInvoice .="<tr><td colspan=3 align=right><b>Total</b></td><td><b>".number_format($BigTotal,2)."</b></td>";
				  $tbInvoice .="<td><b>".number_format($BigTotal2,2)."</b></td></tr>";
				  $tbInvoice .="<tr><td colspan=8 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Fine.Create Invoice ',['clearing/csinvoice'],['data'=>['confirm'=>'Proceed to Create An Invoice?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr></table>";
				  
				  }
				 //kila kitu lazima kiwe poa.
		
		///////////////////////////////////////////////////////////
		
	   
		return $this->render('psinv',[
			'model'=>$model,
			'model2'=>$model2,
			'aname'=>$item[3],
			'iname'=>$item[0],
			'tbPay'=>$tbInvoice,
			'tbJob'=>$this->getJOne($jid),
			'jid'=>$jid,
			'hasItems'=>$hasItems,
		]);
	   
	}
	
	public function actionRmcno($cno)
	{
	   unset($_SESSION['JobItems'][$cno]);
	    return $this->redirect(['psinvoice']);
	}

     public function actionRmcno2($cno)
	{
	   unset($_SESSION['JobFees'][$cno]);
	    return $this->redirect(['psinvoice']);
	}
	
	public function getCItem()
	{
	      $jid = $_SESSION['jid'];
		  $data = array();
		  $conn = Yii::$app->db;
		  if($_SESSION['JobItems'])
		  {
		    foreach($_SESSION['JobItems'] as $ckey=>$val) 
			{
			  $Aselected .=$ckey.",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
		    $q ="SELECT id,CONCAT('BL #: ',blno,' : Container #: ',inumber,' Size: ',size) FROM jobitems ";
			$q .="WHERE jid='$jid' AND NOT id IN('$RS') AND NOT id IN (SELECT iid FROM invoiceitem WHERE iid IS NOT NULL)";
		  }
		  else
		  {
			$q ="SELECT id,CONCAT('BILL OF LADING #: ',blno,' : Container #: ',inumber,' : Size: ',size) FROM jobitems ";
			$q .="WHERE jid='$jid' AND NOT id IN (SELECT iid FROM invoiceitem WHERE iid IS NOT NULL)";
			
		  }	
		$rslt = $conn->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	      $data[$rs[0]] = $rs[1];
	     }
		 return $data;	
	}
	public function getFC()
	{
		  $data = [];
		  $conn = Yii::$app->db;
		 
		  if($_SESSION['JobFees'])
		  {
		    foreach($_SESSION['JobFees'] as $ckey=>$val) 
			{
			  $Aselected .=$ckey.",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
		    $q ="SELECT feeid,name FROM fees WHERE NOT feeid IN('$RS') ORDER BY name";
		  }
		  else
		  {
			$q ="SELECT feeid,name FROM fees ORDER BY name";
			
		  }	
		$rslt = $conn->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	      $data[$rs[0]] = $rs[1];
	     }
		 return $data;	
	}

    
	public function actionCsinvoice()
	{
	  
        $erate = Yii::$app->user->identity->rate;
        $jid = $_SESSION['jid'];
        
        $VatEx = 'Y';
        $VAT = 0;
        $Total = 0;
		$tOne = 0;
	    $BigTotal = 0;
		
		
		$VAT2 = 0;
        $TotalUsd = 0;
		$tOneUsd = 0;
	    $BigTotal2 = 0;
		
		$t2 = 0;
	    $t3 = 0;
		
		$t2Usd = 0;
	    $t3Usd = 0;
		
		$vt = 0;
		$vu = 0;
	     
		$id = Yii::$app->user->id;
	    $CDate = date("Y-m-d");
	 
          $conn = Yii::$app->db;
		  $ino = $conn->createCommand("SELECT invnumber FROM accode")->queryScalar();
		  $cid = $conn->createCommand("SELECT client FROM jobs WHERE jid ='$jid'")->queryScalar();
		  $conn->createCommand("UPDATE accode SET invnumber=invnumber + 1")->execute();
		  $invno = time().$ino;
		  if($_SESSION['JobItems'])
		   {	
		       
			      ///Weka kwenye hii invoice hapa
				  
				  $qt = "SELECT id,charges,inusd,hasvat,vatamt,vatinusd FROM jobdocuments WHERE jid ='$jid' AND paidby !='$cid' AND hascharges='Y' AND invoicenum IS NULL";
				  $dp = $conn->createCommand($qt)->queryAll(false);
				
				    foreach($dp as $p)
				    {
					  $conn->createCommand("UPDATE jobdocuments SET invoicenum ='$invno' WHERE id ='$p[0]'")->execute();
					  
					  $pt = $p[1];
					  $pu = $p[2];
					  if($p[3] == 'Y')
					  {
						 $pt = $p[1] - $p[4];
					     $pu = $p[2] - $p[5]; 
						 
						 $vt = $vt + $p[4];
		                 $vu = $vu + $p[5];
					  }
					  $Total = $Total + $pt; 
					  $TotalUsd = $TotalUsd + $pu; 
				    }
			   foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				      $qpi ="SELECT id,amt,inusd,erate FROM jobitems WHERE id ='$ckey'";
					  
				      $rowIn = $conn->createCommand($qpi)->queryOne(false);
					 
					/* $tOne = $tOne + $rowIn[1];
					 $Total = $Total + $rowIn[1];
					 
					 $tOneUsd = $tOneUsd + $rowIn[2];
					 $TotalUsd = $TotalUsd + $rowIn[2]; */
				    
		         //INSERT Agency Fee		  
		                $qhc ="INSERT INTO invoiceitem(invoicenum,iid,feeid,status,regby,regdate)";
                        $qhc .=" VALUES('$invno','$rowIn[0]',100000,'N','$id','$CDate')";
				
		                $conn->createCommand($qhc)->execute(); 
		          } 
				  
				  foreach($_SESSION['JobFees'] as $ckey=>$val)
		             {
					   $ck = explode(":",$ckey);
					   $qc ="SELECT name,hasvat FROM fees WHERE feeid ='$ck[0]'";
				       $rs = $conn->createCommand($qc)->queryOne(false);
					 
					    $t2 = $t2 + $ck[1];
					    $Total = $Total + $ck[1];
						
						$t2Usd = $t2Usd + $ck[2];
					    $TotalUsd = $TotalUsd + $ck[2];
						if($ck[3] == 'Y')
						{
							 $t3 = $t3 + $ck[1];
							 $t3Usd = $t3Usd + $ck[2];
						}
				        $qhc ="INSERT INTO invoiceitem(invoicenum,feeid,amount,inusd,erate,status,regby,regdate)";
                        $qhc .=" VALUES('$invno','$ck[0]','$ck[1]','$ck[2]','$erate','N','$id','$CDate')";
						 $conn->createCommand($qhc)->execute();
				    }
				  
		        $BigTotal =  $BigTotal + $Total;
				$BigTotal2 =  $BigTotal2 + $TotalUsd;
				$pbt = $BigTotal;
				$pbt2 = $BigTotal2;
				  if($_SESSION['ve'] == 0)
				  {
		             $VAT = ($tOne * 0.18) + ($t3 * 0.18) + $vt;
					 $VAT2 = ($tOneUsd * 0.18) + ($t3Usd * 0.18) + $vu;
					 $VatEx = 'N';
				  }
		
	           $BigTotal = $BigTotal + $VAT;
			   $BigTotal2 = $BigTotal2 + $VAT2;

				$qinv ="INSERT INTO invoice(invoicenum,client,jid,pricebeforetax,pricebeforetax2,vat,vat2,tamount,inusd,erate,";
				$qinv .="status,invoicedate,regby,regdate,vatexempted,crtime,isproforma) VALUES('$invno','$cid','$jid','$pbt','$pbt2','$VAT','$VAT2',"; 
                $qinv .="'$BigTotal','$BigTotal2','$erate','N','$CDate','$id','$CDate','$VatEx',CURTIME(),'N')";
		              $conn->createCommand($qinv)->execute();

                      $conn->createCommand("UPDATE jobs SET stid = 9,eby='$id',edate = CURDATE() WHERE jid ='$jid'")->execute();					  
		
		                unset($_SESSION['ve']);
		                unset($_SESSION['Jobitems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['JobFees']);  //remove ALL chassis number to the session variable
						unset($_SESSION['jid']);
						
	      }
		  return $this->redirect(array('clearing/printinv','ino'=>$invno));
	    
	}
	
	public function actionReprintinv()
	{
		$model = new Inv();
		if(isset($_POST['btnReprint']))
	    {
	       if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
		   $invno = trim($model->invno);
		   $conn = Yii::$app->db;
		   $cnt =$conn->createCommand("SELECT COUNT(invoicenum) FROM invoice WHERE invoicenum = '$invno'")->queryScalar();
		  
		   if($cnt > 0)
		   { 
		     return $this->redirect(['clearing/printinv','ino'=>$invno]);
		   } 
		   else
		   {
		     Yii::$app->session->setFlash('error',"Invoice Number [$invno] Provided does not exist in the system.");
		   }
		   }
	   }
	   return $this->render('reinv',['model'=>$model]);
	}
	
	public function actionPrintinv($ino)
	{
	  date_default_timezone_set('Africa/Nairobi');
	  
	  //$this->layout ='print';
	  $Total = 0;
      $VAT = 0;
	  $t2 = 0;
      $BigTotal = 0;
	  $totalDoc = 0; 
	  $totalDocUsd = 0; 
	  
      putenv("TZ=Africa/Nairobi");
	    $erate = Yii::$app->user->identity->rate;
        $conn = Yii::$app->db;
		$cid = $conn->createCommand("SELECT j.cfid FROM jobs j INNER JOIN invoice i ON j.jid = i.jid WHERE i.invoicenum ='$ino'")->queryScalar();
        $qci ="SELECT cname,paddress,box,telephone,fax,mob,region,vat,tin,email FROM companyinfo WHERE cid ='$cid'";
		$cd = $conn->createCommand($qci)->queryOne(false);
		
		
		 $qpr ="SELECT i.client,i.wamount,i.wpercent,tamount,i.erate,i.vat,i.amountbw,i.cpaid,i.vat2,i.inusd,i.jid,c.box,c.paddress,c.tin,c.vat ";
		 $qpr .="FROM companyinfo c INNER JOIN invoice i ON c.cname = i.client WHERE i.invoicenum ='$ino'";
		 
		 $rs = $conn->createCommand($qpr)->queryOne(false);
		  $tbBank ="";
		/*$tbBC ="<table cellspacing=0 width=100% >";
        $tbBC .="<tr><td><h1>:::.PROFORMA INVOICE.:::</h1></td><td align=right>";
		$tbBC .="<img src=".Yii::app()->createAbsoluteUrl('barcodegenerator/generatebarcode',array('code'=>$ino))."><br />&nbsp;</td>";
		$tbBC .="</tr></table>";
		
		
		if($cid == 1)
		{
		 
		  $tbBank .="<table cellpadding=1 border=1 cellspacing=0 width=100% >";
          $tbBank .="<tr bgcolor='#ABE1FE'><td colspan=6>&nbsp;<b>OUR BANK DETAILS</td></tr>";
		  $tbBank .="<tr><td rowspan=2><b>MC JURO INVESTMENTS LTD</b></td><td rowspan=2><b>&nbsp;&nbsp;CRDB BANK<br />&nbsp;&nbsp;HOLLAND BRANCH&nbsp;&nbsp;&nbsp;</b></td>";
		  $tbBank .="<td><i>T.SHS ACCOUNT</i><td>&nbsp;<b>01J1019877300</b></td><td><i>&nbsp;&nbsp;SWIFT&nbsp;&nbsp;</i></td><td><b>CORUTZTZ</b></td></tr>";
		  $tbBank .="<tr><td><i>USD ACCOUNT</i><td>&nbsp;<b>02J1019877300</b></td><td><i>&nbsp;&nbsp;SWIFT&nbsp;&nbsp;</i></td><td><b>CORUTZTZ</b></td></tr>";
		  $tbBank .="</table>";
		  
		$tbIheader .="<tr><td rowspan=5><img src=images/print_logo.png width=194 height=101 /></td>";
		}
		else
		{
		
		  $tbBank .="<table cellpadding=1 border=1 cellspacing=0 width=100% >";
          $tbBank .="<tr bgcolor='#ABE1FE'><td colspan=6>&nbsp;<b>OUR BANK DETAILS</td></tr>";
		  $tbBank .="<tr><td rowspan=2><b>AHSAM GENERAL TRADERS LTD</b></td><td rowspan=2><b>&nbsp;&nbsp;CRDB BANK<br />&nbsp;&nbsp;QUALITY CENTER</b></td>";
		  $tbBank .="<td><i>T.SHS ACCOUNT</i><td>&nbsp;<b>&nbsp;</b></td><td><i>&nbsp;&nbsp;SWIFT&nbsp;&nbsp;</i></td><td><b>CORUTZTZ</b></td></tr>";
		  $tbBank .="<tr><td><i>USD ACCOUNT</i><td>&nbsp;<b>250460418900</b></td><td><i>&nbsp;&nbsp;SWIFT&nbsp;&nbsp;</i></td><td><b>CORUTZTZ</b></td></tr>";
		  $tbBank .="</table>";
		  
		 
		}*/
		$tbIheader ="<table cellpadding=1 border=1 cellspacing=0 width='80%'>";
		 $tbIheader .="<tr><td rowspan=5><img src=../img/socean.png width=250 height=80 /></td>";
        $tbIheader .="<td colspan=2><b><font size=+1>$cd[0]</font></b></td></tr>";
        $tbIheader .="<tr><td>$cd[1]</td><td>VAT:&nbsp;<b>$cd[7]</b></td></tr>";
        $tbIheader .="<tr><td>P.O. Box:&nbsp;$cd[2]&nbsp;&nbsp;$cd[6]</td><td>TIN:&nbsp;<b>$cd[8]</b></td></tr>";
	    $tbIheader .="<tr><td>Telephone:&nbsp;$cd[3]&nbsp;&nbsp;Fax:&nbsp;$cd[4]</td>";
	    $tbIheader .="<td>&nbsp;</td></tr>";
		$tbIheader .="<tr><td>Mobile:&nbsp;$cd[5]</td><td>Email:&nbsp;$cd[9]</td></tr></table>";
			$wamount = $rs[1];
			$wperc = $rs[2];
			$TAmount = $rs[3];
			$ERate = $rs[4];
			$theVAT = $rs[5]; 
			$abw = $rs[6];
			$theVAT2 = $rs[8]; 
			$inUSD = $rs[9];  
			$jid = $rs[10];  
			//$TUAmount = $TAmount / $ERate;
		
        $tbCust ="<table cellpadding=1 border=1 cellspacing=0 width='80%'>";
        $tbCust .="<tr><td align=right>To :</td><td><b>$rs[0]</b></td><td>TIN:&nbsp;<b>$rs[13]</b></td><td align=right>Date :</td>";
        $tbCust .="<td>&nbsp;<b>".date("d/m/Y")."</b></td></tr>";
		$tbCust .="<tr><td align=right>&nbsp;</td><td><b>$rs[12]</b></td><td>VRN:&nbsp;<b>$rs[14]</b></td><td align=right>Currency :</td>";
        $tbCust .="<td>&nbsp;<b>TSH | USD</b></td></tr>";
        $tbCust .="<tr><td align=right>P.O Box :</td><td colspan=4><b>$rs[11]</b></td></tr>";
        $tbCust .="<tr><td align=right>Invoice # :</td><td colspan=2><b>$ino</b></td><td align=right>Valid For :</td><td>&nbsp;<b>NA</b></td></tr></tr></table>";
 
                 $bl = $conn->createCommand("SELECT blno FROM jobitems WHERE jid ='$jid' LIMIT 1")->queryScalar();
                  $qi ="SELECT j.name,j.blno,j.quantity,j.client FROM jobs j INNER JOIN invoice i ON j.jid = i.jid ";
		          $qi .="WHERE i.invoicenum ='$ino'";
		          $item = $conn->createCommand($qi)->queryOne(false);
 
                  $tbInvoice ="<table cellpadding=1 border=1 cellspacing=0 width='80%'>";
		          $tbInvoice .="<tr bgcolor='#ABE1FE'><td>&nbsp;<b>Description</b></td>";
		          $tbInvoice .="<td>&nbsp;<b>Charges</b></td><td>&nbsp;<b>Amount [TSH]</b></td><td>&nbsp;<b>Amount [USD]</b></td></tr>";
				  
				  $qt = "SELECT d.name,jd.charges,jd.inusd FROM jobdocuments jd INNER JOIN documents d ON d.docid = jd.docid ";
		          $qt .= "WHERE jd.paidby !='$item[3]' AND jd.paid='N' and jd.hascharges ='Y' AND jd.invoicenum ='$ino'";
	
				  $dp = $conn->createCommand($qt)->queryAll(false);
				  if(!empty($dp))
				  {
				    foreach($dp as $p)
				    {
				     $tbInvoice .="<tr><td align=right> $item[0] in BL #:&nbsp;<b>$bl</b></td>";
					 $tbInvoice .="<td align=right>$p[0]&nbsp;</td><td>".number_format($p[1],2)."</td><td>".number_format($p[2],2)."</td></tr>";
					 $totalDoc = $totalDoc + $p[1]; 
					 $totalDocUsd = $totalDocUsd + $p[2]; 
					 
				    }
				$tbInvoice .="<tr><td colspan=2 align=right><b>Sub Total&nbsp;</b></td><td><b>".number_format($totalDoc,2)."</b></td>";
				$tbInvoice .="<td><b>".number_format($totalDocUsd,2)."</b></td></tr>";
				  }
				   $qpi ="SELECT CONCAT('Container #: ',ji.inumber,' - ',ji.size,' , Items: ',ji.descr),i.amount,i.inusd FROM jobitems ji INNER JOIN invoiceitem i ON ";
				   $qpi .="ji.id = i.iid WHERE i.invoicenum ='$ino'";
				   $rst = $conn->createCommand($qpi)->queryAll(false);
				  
				  foreach($rst as $rs)
		          {
					 
					// $tOne = $tOne + $rs[2];
					// $tOneUsd = $tOneUsd + $rs[3];
					
				     $tbInvoice .="<tr><td colspan=2>$rs[0]&nbsp;</td><td>&nbsp;</td>";
					 $tbInvoice .="<td>&nbsp;</td></tr>";
				  }
				  
                  // $tbInvoice .="<tr><td colspan=2 align=right><b>Sub Total</b></td><td><b>".number_format($tOne,2)."</b></td>";
				  // $tbInvoice .="<td><b>".number_format($tOneUsd,2)."</b></td></tr>";
				  
				  $qp2 ="SELECT f.name,i.amount,i.inusd FROM fees f INNER JOIN invoiceitem i ON ";
				   $qp2 .="f.feeid = i.feeid WHERE i.invoicenum ='$ino' ORDER BY i.feeid DESC";
				   $rst2 = $conn->createCommand($qp2)->queryAll(false);
				  
				  foreach($rst2 as $rs)
		          {
					 
					 $t2 = $t2 + $rs[1];
					  $t2Usd = $t2Usd + $rs[2];
				 $tbInvoice .="<tr><td align=right colspan=2>$rs[0]&nbsp;</td><td>".number_format($rs[1],2)."</td><td>".number_format($rs[2],2)."</td></tr>";
				  }
				  if($t2 > 0)
				  {
				   $tbInvoice .="<tr><td colspan=2 align=right><b>Sub Total</b></td><td><b>".number_format($t2,2)."</b></td>";
				   $tbInvoice .="<td><b>".number_format($t2Usd,2)."</b></td></tr>";
				  }
				  $tbInvoice .="<tr><td colspan=2 align=right><b>VAT - 18%</b></td><td><b>".number_format($theVAT,2)."</b></td>";
				  $tbInvoice .="<td><b>".number_format($theVAT2,2)."</b></td></tr>";
				  $tbInvoice .="<tr><td colspan=2 align=right><b>Total</b></td><td><b>".number_format($TAmount,2)."</b></td>";
				  $tbInvoice .="<td><b>".number_format($inUSD,2)."</b></td></tr></table>";
				  

		  $tbInvoice .="<tr><td colspan=4>&nbsp;Invoice Prepared by: &nbsp;<i><b>".Yii::$app->user->identity->fn."</b></i>";
		  $tbInvoice .="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Printed Date:&nbsp;<b>".date("d/m/Y")."</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Time:&nbsp;<b>".date("h:i:s")."</b></td></tr></table>";
		  
		 /* $fname = time().'.pdf';
		  $fname2 = $_SERVER['DOCUMENT_ROOT'].'macjuro/cfdocs/'.$fname;
		  $mpdf = new mpdf();
		  $mpdf->SetFooter('Generated on:'.date("d/m/Y"));
		  $stylesheet = file_get_contents(Yii::getPathOfAlias('webroot.css') . '/pdfrpt.css');
	      $mpdf->WriteHTML($stylesheet,1);
		  $mpdf->WriteHTML($tbBC.'<br />'.$tbIheader.'<br />'.$tbCust.'<br />'.$tbInvoice.'<br />&nbsp;<br />&nbsp;<br />'.$tbBank);
		  
		  $mpdf->Output($fname2,'F');
		  $this->progEmail($jid,$fname);
		  $mpdf->Output();
		   exit;*/
		  return $this->renderAjax('vwinvoice',array('tbBC'=>$tbBC,'tbHeader'=>$tbIheader,'tbCust'=>$tbCust,'tbInv'=>$tbInvoice,'tbBank'=>'')); 
		 
	}
	
	
	//////////////////////////////////////////////////////////////////////////////////////////////
	
	public function getSQI($cid)
	{
	   $data = [];
	   
		  
		  $conn = Yii::$app->db;
		  if($_SESSION['JobItems'])
		  {
		    foreach($_SESSION['JobItems'] as $ckey=>$val) 
			{
			 $Aselected .=$ckey.",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
		    $q ="SELECT oi.id,oi.cno,oi.iid FROM orderitems oi INNER JOIN orders o ON o.orderno = oi.orderno ";
	        $q .="WHERE o.cid = '$cid' AND oi.shipped ='N' AND NOT oi.id IN('$RS') ORDER BY oi.cno"; 
		  }
		  else
		  {
			//$q ="SELECT oi.cno,CONCAT(oi.cno,' - ',i.name) FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid ";
	        //$q .="WHERE oi.orderno = '$oid' ORDER BY oi.cno"; 
            $q ="SELECT oi.id,oi.cno,oi.iid FROM orderitems oi INNER JOIN orders o ON o.orderno = oi.orderno ";
	        $q .="WHERE o.cid = '$cid' AND oi.shipped = 'N' ORDER BY oi.cno";			
		  }
		$rslt = $conn->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	       $items = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($rs[2]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($items as $item)
					  {
						  $it .= $item[0].' , ';
						
					  }
					  $it = rtrim($it,' , ');
		   $data[$rs[0]] = $rs[1].' - '.$it;
	     }

		 return $data;	
	}
	
	public function getSQI2($id)
	{
	   $data = [];
	   
		  
		  $conn = Yii::$app->db;
		  if($_SESSION['JobItems'])
		  {
		    foreach($_SESSION['JobItems'] as $ckey=>$val) 
			{
			 $Aselected .=$ckey.",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
		    $q ="SELECT oi.id,g.cper,oi.cno,oi.iid FROM orderitems oi INNER JOIN itemgroup g ON g.gid = oi.gid ";
	        $q .="WHERE oi.id = '$id' AND oi.shipped ='N' AND NOT oi.id IN('$RS') ORDER BY oi.cno"; 
		  }
		  else
		  {
			//$q ="SELECT oi.cno,CONCAT(oi.cno,' - ',i.name) FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid ";
	        //$q .="WHERE oi.orderno = '$oid' ORDER BY oi.cno"; 
            $q ="SELECT oi.id,g.cper,oi.cno,oi.iid FROM orderitems oi INNER JOIN itemgroup g ON g.gid = oi.gid ";
	        $q .="WHERE oi.id = '$id' AND oi.shipped = 'N' ORDER BY oi.cno";			
		  }
		$rslt = $conn->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	       $items = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($rs[3]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($items as $item)
					  {
						  $it .= $item[0].' , ';
						
					  }
					  $it = rtrim($it,' , ');
		   $data[$rs[0].':'.$rs[1]] = $rs[2].' - '.$it;
	     }

		 return $data;	
	}


public function actionPmovement($cid)
	{
		$r = Yii::$app->db->createCommand("SELECT box,region,telephone,mob,tin,vat FROM companyinfo")->queryOne(0);
		
		$qp ="SELECT o.orderno,oi.batchno,oi.barcode,p.name,l.name,olocid FROM orderitems oi INNER JOIN products p  ";
		$qp .="ON p.prodid = oi.prodid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN ";
		$qp .="locations l ON l.locid = oi.locid WHERE oi.movcode ='$cid'";
        
        $rst = Yii::$app->db->createCommand($qp)->queryAll(false);
        if (!empty($rst)) 
		{
             $logo ="<img src='".Yii::getAlias('@web') .'/img/frostan.png'."' width ='160' height ='50'><br /><br />";
			 $tbH = "<table width=100%>";
			 $tbH .= "<tr><td><center>$logo</center></td></tr>";
			 $tbH .= "<tr><td><center>P.O Box $r[0]/$r[1]/Tanzania</center></td></tr>";
			 $tbH .="<tr><td><center>Phone: $r[2] / Mobile: $r[3]</center></td></tr>";
			 $tbH .="<tr><td><center><b>TIN: $r[4] VRN: $r[5]</center></td></tr></table><br >";
			 
		    
			$tbTr = "<table cellpadding=1 border=1 cellspacing=0 width=100% >";
			$tbTr .="<tr><td colspan=5><b>STOCK MOVEMENT FORM</b></td><td colspan=2>&nbsp; MOVEMENT CODE :<b>$cid</b></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>ORDERNO</b></td><td><b>BATCHNO</b></td>";
            $tbTr .="<td><b>BARCODE</b></td><td><b>PRODUCT</b></td><td><b>OLD LOCATION</b></td><td><b>NEW LOCATION</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
                $oloc = Yii::$app->db->createCommand("SELECT name FROM locations WHERE locid = '$rs[5]'")->queryScalar(); 
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td><center>$rs[1]</center></td>";
				$tbTr .="<td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]</td><td>$oloc</td></tr>";
				$i++;
            }
            $tbTr .="</table><br />&nbsp;<br />&nbsp;<br />";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		     $tbF = "<table width=100%>";
			 $tbF .= "<tr><td width=16%>&nbsp;</td><td>Requested By......................</td>";
			 $tbF .= "<td width=20%>&nbsp;</td><td>Stock Manager......................</td></tr>";
			 $tbF .= "<tr><td width=16%>&nbsp;</td><td>Signature......................</td>";
			 $tbF .= "<td width=20%>&nbsp;</td><td>Signature......................</td></tr></table><br >";
		
            date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				'mode' => Pdf::MODE_CORE, 
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $tbH.$tbTr.$tbF,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Cutting Preparation Sheet'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
			 
		   /* $pdf = Yii::$app->pdf;
			$pdf->orientation = ORIENT_LANDSCAPE;
			//$pdf->methods->setHeader = 'Cutting Preparation Sheet';
			$pdf->methods->setFooter = 'Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}';
            $pdf->content = $tbH.$tbTr.$tbF;
            return $pdf->render(); */
		  
	}
	
	
	/////////////////////////////////////////////////////////////////////////////////
	///Cargo Loading to Container
	
	public function actionShowitc($scode)
	{
		return $this->render('vwo',['tbData'=>$this->getCntItems($scode)]);
	}
	
	public function getCntItems($scode)
	{
		
	    $qs = "SELECT sl.name,oi.containerno,DATE_FORMAT(oi.expsdate,'%d/%m/%Y'),DATE_FORMAT(oi.expardate,'%d/%m/%Y') ";
		$qs .="FROM orderitems oi INNER JOIN sline sl ON sl.slid = oi.slid ";
		$qs .="WHERE oi.scode =:scode LIMIT 1";
		
		$dt = Yii::$app->db->createCommand($qs)->bindParam(':scode',$scode)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table class='table table-bordered table-gray'><tr><td colspan=2><i>Shipping Code : </i><b>$scode</b> &nbsp;&nbsp;&nbsp;";
			 $tbH .="</td></tr>";
			 $tbH .="<thead><tr><th colspan=2>SHIPPING DETAILS</th></tr></thead>";
			 $tbH .="<tr><th width=20%>Shipping Line</th><td>$dt[0]</td></tr>";
		     $tbH .="<tr><th>Container No</th><td>$dt[1]</td></tr>";
			 $tbH .="<tr><th>Expected Shipping Date</th><td>$dt[2]</td></tr>";
			 $tbH .="<tr><th>Expected Arrival Date</th><td>$dt[3]</td></tr>";
			 $tbH .="</table>";
		
		  $qo = "SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name FROM orderitems oi INNER JOIN itemgroup g ";
		  $qo .="ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
		  $qo .="WHERE oi.scode =:scode ";
		  $orders = Yii::$app->db->createCommand($qo)->bindParam(':scode',$scode)->queryAll(false);
	
		if(!empty($orders))
		{ 
	        $i = 1;
			$Total = 0;
			
			$tbO .="<table class='table table-bordered table-gray footable'><thead><tr><th colspan=8><b>ITEMS LOADED</b></th></tr>";
		    $tbO .="<tr><th>SN</th><th>Control Number</th><th>Item Group</th><th>Items</th><th>CBM</th><th>Price (USD)</th>";
		    $tbO .="<th>Client</th><th>Shipping As</th></tr></thead>";
		     foreach($orders as $order)
			  {
			     if($order[2] == 'Y')
					  {
						
						$cbm = $order[3];
						
					  }
					  else
					  {
						 
						 $cbm = 'NA'; 
					  }
					  
				$rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($order[1]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					$it = rtrim($it,' , ');
					  
					  $Total = $Total + $order[4];
	                  $tbO .="<tr><td>$i</td><td>$order[0]</td><td>$order[7]</td><td>$it</td><td>$cbm</td>";
				      $tbO .="<td>".number_format($order[4],2)."</td><td>$order[5]</td><td>$order[6]</td></tr>";
				      $i++;
			  }
			   $tbO .=" <tfoot><tr><td colspan=5 align=right><b><i>Total:</i></b></td>";
			   $tbO .="<th colspan=3>".number_format($Total,2)."</th></tr></tfoot></table>";
		  }
		 }  
		return $tbH.$tbO;
		
	}
	
	/////////////////////////////////////////////////////////////////////////
	//Edit Container Loading
	public function actionEclstep1()
    {
        
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		
          $q2 ="SELECT oi.scode 'Shipping Code',sl.name as 'Shipping Line',oi.containerno as 'Container No',";
		  $q2 .="DATE_FORMAT(oi.expsdate,'%d/%m/%Y') as 'Shipping Date',DATE_FORMAT(oi.expardate,'%d/%m/%Y') as 'Arrived Date', ";
		  $q2 .="COUNT(oi.iid) AS 'Loaded Cargo' FROM orderitems oi INNER JOIN sline as sl ON sl.slid = oi.slid WHERE oi.shipped = 'Y' ";$q2 .="AND oi.hasbl = 'N' GROUP BY oi.scode,sl.name,oi.containerno,oi.expsdate,oi.expardate ORDER BY oi.expsdate DESC";
		
		$cn = Yii::$app->db->createCommand($q2)->queryAll();
		$cnt = count($cn);
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Shipping Code','Shipping Line'],],
							'pagination'=>[
							               'pageSize'=>15,
	                                      ],
						    ]);	
      
         	  
		return $this->render('slhist2',['dataProvider'=>$dataProvider]);
    }
	
	public function actionLsdet($scode)
	{
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		    unset($_SESSION['slid']);
			unset($_SESSION['scode']);
			unset($_SESSION['JobItems']);
			unset($_SESSION['containerno']);
			unset($_SESSION['expsdate']);
			unset($_SESSION['expardate']);
			
			$q = "SELECT slid,containerno,DATE_FORMAT(expsdate,'%d/%m/%Y'),DATE_FORMAT(expardate,'%d/%m/%Y') FROM orderitems ";
			$q .="WHERE scode = '$scode'";
			$rs = Yii::$app->db->createCommand($q)->queryOne(false);
		
			 $_SESSION['slid'] = $rs[0];
			 $_SESSION['scode'] = $scode;
			 $_SESSION['containerno'] = $rs[1];
			 $_SESSION['expsdate'] = $rs[2];
			 $_SESSION['expardate'] = $rs[3];
			 
			 ////////////////////////////////////////////////////////////////
			 //Load Container Cargo As It was Previously
			 
			 $ids = Yii::$app->db->createCommand("SELECT id FROM orderitems WHERE scode ='$scode' AND hasbl = 'N'")->queryAll(false);
			 foreach($ids as $id)
			 {
				 $_SESSION['JobItems'][$id[0]]++; 
			 }
			 return $this->redirect(['operation/eloading']);
		 
		
	}
	
	public function actionSavebl()
	{
		     $Total = 0;
			 $id = Yii::$app->user->id;
	  if($_SESSION['JobItems2'])
		{	
          foreach($_SESSION['JobItems2'] as $cke=>$vali)
		        {
                 	$ck = explode(":",$cke);
				    foreach($_SESSION['CntCargos'] as $ckey=>$val)
		            {
		                $qd ="UPDATE orderitems SET hasbl = 'Y',bltype = '$ck[0]',blno ='$ck[1]', blby = '$id', bldate =NOW() ";
						$qd .="WHERE scode ='$ckey'";
						Yii::$app->db->createCommand($qd)->execute(); 
		            }
					  
		               		
                }
		}	
		                unset($_SESSION['CntCargos']);  //remove ALL chassis number to the session variable
						unset($_SESSION['JobItems2']);  //remove ALL chassis number to the session variable
						
			
			 Yii::$app->session->setFlash('osuccess','Container BL Has Been Successful Saved');
			 $this->redirect(['operation/showbl','blno'=>$ck[1]]);
	}

	
	public function actionShowbl($blno)
	{
		  
		   $q = "SELECT sl.name,oi.containerno,oi.scode,COUNT(oi.scode),SUM(oi.price),DATE_FORMAT(oi.expardate,'%d/%m/%Y') ";
		   $q .="FROM orderitems oi INNER JOIN sline sl ON sl.slid = oi.slid WHERE oi.blno = '$blno' ";
           $q .="GROUP BY sl.name,oi.containerno,oi.scode";			  
		   $rst = Yii::$app->db->createCommand($q)->queryAll(false);
		   if(!empty($rst))
			{
		     $Total = $number = 0;
             $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Shipping Line</th><th>BL Number</th>";
		     $tbInvoice .="<th>Container #</th><th>Total Cargos</th><th>Total Amount</th><th>Exp. Arrival Date</th></tr>";
	
				  $Total = $Number = 0;
				  $i = 1;
				  foreach($rst as $rs)
		          { 
					 $Total = $Total + $rs[4];
					 $Number = $Number + $rs[3];
				     $tbInvoice .="<tr><td>$i</td>";
		             $tbInvoice .="<td>$rs[0]</td><td><b>$blno</b></td><td>$rs[1]</td><td>".number_format($rs[3])."</td>";
					 $tbInvoice .="<td><b>".number_format($rs[4])."</b></td><td>$rs[5]</td><tr>";
				
					 $i++; 
				  }
			         $tbInvoice .="<tr><td colspan=4 align=right><b>Total</b></td><td><b>".number_format($Number)."</b></td>";
			         $tbInvoice .="<td colspan=2><b>".number_format($Total,2)."</b></td></tr></table>";
				 }
				 else
				 {	  
			       $tbInvoice ="<tr><td><b><i>Invalid Request</i></b></td></tr></table>";		 
			
				 }
		
		return $this->render('vwo',['tbData'=>$tbInvoice]);
	}
	
	
	public function actionNewjob()
	{
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }

		  $model2 = new Jobs;
		  $models = $this->getContainers();
		  $hasTotal = false;
		  $hasItems2 = false;
		  
		  if(isset($_POST['btnAdd2'])) 
		  {  
		  if($model2->load(Yii::$app->request->post()) && $model2->validate())
		   {
				//add this item to the session variable
                unset($_SESSION['Job']);				
                $_SESSION['Job'][$model2->client.':'.$model2->icd.':'.$model2->tansno.':'.$model2->cfid]++;   
			    $this->refresh(); 
				
		   }
          }
		 if(isset($_POST['btnAdd'])) 
		  {  
			    foreach($models as $i=>$mod)
			    {
					
			      $mod[6]->attributes = $_POST['Containers'][$i];
				  if($mod[6]->picked == 1)
				   {
					  $_SESSION['Cnt'][$mod[6]->containerno]++; 
				   }
			    }
			
			  $models = $this->getContainers();
          }
		  
		   $i = 1;
		   $Total = $number = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Shipping Line</th><th>BL #</th>";
		   $tbInvoice .="<th>Container #</th><th>Total Cargos</th><th>Exp. Arrival Date</th></tr>";
		   
		   if($_SESSION['Cnt'])
				 {
				  $hasItems = true;
				  $Total = 0;
				  $i = 1;
				  foreach($_SESSION['Cnt'] as $ckey=>$val)
		          {
					 $qi = "SELECT sl.name,oi.blno,oi.containerno,DATE_FORMAT(oi.expardate,'%d/%m/%Y'),COUNT(oi.scode) ";
					  $qi .="FROM orderitems oi INNER JOIN sline sl ON sl.slid = oi.slid WHERE oi.containerno = '$ckey' ";
                      $qi .=" AND oi.jid IS NULL GROUP BY sl.name,oi.blno,oi.containerno,oi.expardate";			  
					  $ck = Yii::$app->db->createCommand($qi)->queryOne(0);
					  
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['clearing/rmcnt','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$ck[0]</td><td>$ck[1]</td><td>$ck[2]</td><td>".number_format($ck[4])."</td>";
					 $tbInvoice .="</td><td>$ck[3]</td><tr>";
				
					 $i++; 
				  }
			 // $tbInvoice .="<tr><td colspan=4 align=right><b>Total</b></td><td><b>".number_format($number)."</b></td>";
			  //$tbInvoice .="<td colspan=2><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 
				 if($_SESSION['Job'])
				 {
				  $hasItems2 = true;
				  $tt2 = 0;
				  foreach($_SESSION['Job'] as $ckey=>$val)
		          {
					$ck = explode(":",$ckey);
					$icd = Yii::$app->db->createCommand("SELECT name FROM icds WHERE icd = '$ck[1]'")->queryScalar();
					$cf = Yii::$app->db->createCommand("SELECT cname FROM companyinfo WHERE cid = '$ck[3]'")->queryScalar();
					  	  
			$tbInvoice .="<tr bgcolor='#D3D3D3'><th colspan=7><b>Clearing Particulars</b></th></tr>";
             $tbInvoice .="<tr><td><b>Option</b></td><td><b>Customer</b></td><td><b>ICD</b></td><td><b>Tansad #</b></td>";
			 $tbInvoice .="<td colspan=3><b>Job Handled By</b></td></tr>";			 
			$tbInvoice .="<tr><td><b>".Html::a("<b>Remove</b>",['clearing/rmjb','cid'=>$ckey])."</b></td><td>$ck[0]</td>";
			$tbInvoice .="<td>$icd</td><td>$ck[2]</td><td colspan=3>$cf</td></tr>";
		    $tbInvoice .="<tr><td align='right' colspan=7><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Register This Job',['clearing/savejob'],['data'=>['confirm'=>'Register This Job?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				
					 
				  }
				 }
				 
				 $tbInvoice .="</table>";
		         return $this->render('_fncjob',['models'=>$models,'model2'=>$model2,
		                               'tbInv'=>$tbInvoice,'hasItems'=>$hasItems,'cid'=>$cid]);
		  

		  
	}
	
	public function actionSavejob()
	{
	  
	  if($_SESSION['Job'])
		{	
          $id = Yii::$app->user->id;
		  $jid = $this->getJID();
		  $par = ''; 
		  foreach($_SESSION['Job'] as $job=>$val)
		    {
                 $par = explode(':',$job);
				 $i = 0;				 
				 foreach($_SESSION['Cnt'] as $ckey=>$val)
					{
	$cno = Yii::$app->db->createCommand("SELECT slid,blno,bltype,cntsize,cno FROM orderitems WHERE containerno = '$ckey' AND jid IS NULL LIMIT 1")->queryOne(0);
	$it = Yii::$app->db->createCommand("SELECT iid FROM orderitems WHERE containerno = '$ckey' AND jid IS NULL")->queryAll(false);
	$data = '';
	      foreach($it as $t)
		  {
			  $cnt = 1;
			$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$cno[4]'")->queryScalar();
			if($ccn)
			{
				$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
			}
			$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$cno[4]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].': Items : '.$rs[2].' : Descr : '.$rs[3].' , ';
						
					  }
					$it = rtrim($it,' , ');
		  }
		  $data = rtrim($data,',');
					 		   
		   $q = "INSERT INTO jobitems(jid,slid,blno,bltype,inumber,size,descr,cby,cdate) ";
		   $q .="VALUES('$jid','$cno[0]','$cno[1]','$cno[2]','$ckey','$cno[3]','$data','$id',NOW())";
			Yii::$app->db->createCommand($q)->execute();
			Yii::$app->db->createCommand("UPDATE orderitems SET jid = '$jid' WHERE containerno = '$ckey' AND jid IS NULL")->execute();
			$i++;
									
					}
			}
			$qjob = "INSERT INTO jobs(jid,cfid,client,name,quantity,icd,stid,tansno,cby,cdate) VALUES";
			$qjob .="('$jid','$par[3]','$par[0]','CONTAINER','$i','$par[1]',1,'$par[2]','$id',NOW())";
			Yii::$app->db->createCommand($qjob)->execute();
			unset($_SESSION['Job']);  //remove ALL chassis number to the session variable
			unset($_SESSION['Cnt']);  //remove ALL chassis number to the session variable
						
			 Yii::$app->session->setFlash('jsuccess','Clearing Job Has Been Successful Created');
			 $this->redirect(['clearing/viewone','jid'=>$jid]);
		}
	}
	
	
	public function actionPermits($jid)
	{
		$model = new Jobdocuments;
		$model->jid = $jid;
		if($model->load(Yii::$app->request->post()))
		 {
			$model->docattach = UploadedFile::getInstance($model,'docattach');
			if($model->save())
			{
	           if($model->docattach)
						{
					     $form = $model->docid.'_'.time().'.'.$model->docattach->extension;
					     $model->docattach->saveAs(Yii::getAlias('@app/cfdocs/' .$form));
						 Yii::$app->db->createCommand("UPDATE jobdocuments SET docattach ='$form' WHERE id ='$model->id'")->execute();
						  
						}
						else
						{
					  Yii::$app->db->createCommand("UPDATE jobdocuments SET docattach ='nodoc.png' WHERE id ='$model->id'")->execute();
						}
				 Yii::$app->session->setFlash('jsuccess','Job Progress details Successfull saved');
				//$this->progEmail($model->id);
				return $this->redirect(['viewone','jid'=>$model->jid]);
			}	   
		}
        $cname = Yii::$app->db->createCommand("SELECT client FROM jobs WHERE jid ='$jid'")->queryScalar();
		return $this->render('_fccert',['model'=>$model,'cname'=>$cname]);
		
	}
	
	public function actionJobupdates($jid)
	{
		$model = new Jobdocuments;
		$model->jid = $jid;
		if($model->load(Yii::$app->request->post()))
		 {
			$model->docattach = UploadedFile::getInstance($model,'docattach');
			if($model->save())
			{
	           if($model->docattach)
					{
					    $form = $model->docid.'_'.time().'.'.$model->docattach->extension;
					    $model->docattach->saveAs(Yii::getAlias('@app/cfdocs/' .$form));
						Yii::$app->db->createCommand("UPDATE jobdocuments SET docattach ='$form' WHERE id ='$model->id'")->execute();
						 
						$theid = Yii::$app->db->createCommand("SELECT stid FROM documents WHERE docid ='$model->docid'")->queryScalar();
						Yii::$app->db->createCommand("UPDATE jobs SET stid ='$theid' WHERE jid ='$jid'")->execute();
					}
					else
					{
						$theid = Yii::$app->db->createCommand("SELECT stid FROM documents WHERE docid ='$model->docid'")->queryScalar();
						Yii::$app->db->createCommand("UPDATE jobs SET stid ='$theid' WHERE jid ='$jid'")->execute();
						  
						Yii::$app->db->createCommand("UPDATE jobdocuments SET docattach ='nodoc.png' WHERE id ='$model->id'")->execute();
					}
						Yii::$app->session->setFlash('jsuccess','Job Progress details Successfull saved');
						//$this->progEmail($model->id);
				$this->redirect(['viewone','jid'=>$model->jid]);
			}	   
		}
         $cname = Yii::$app->db->createCommand("SELECT client FROM jobs WHERE jid ='$jid'")->queryScalar();
		 return $this->render('_fcdoc',['model'=>$model,'cname'=>$cname]);
		$this->render('_fcdoc',array(
			'model'=>$model,'cname'=>$cname
		));
		
	}
	
	public function actionDoview($jid)
	{
		return $this->render('vwone',['tbOne'=>$this->getOne($jid)]);
	}
	
	public function actionViewone($jid)
	{
		return $this->render('vwone',['tbOne'=>$this->getOne($jid)]);
	}
	
	public function getOne($jid)
	{
		$conn = Yii::$app->db;
		
	    $q = "SELECT j.client,ji.blno,ji.bltype,j.name,j.quantity,s.name,ic.name,js.name,cf.cname FROM jobs j INNER JOIN jobitems ji ";
	    $q .="ON j.jid = ji.jid INNER JOIN sline s ON s.slid = ji.slid INNER JOIN icds ic ON ic.icd = j.icd INNER JOIN jobstage js ON ";
		$q .="js.stid = j.stid INNER JOIN companyinfo cf ON cf.cid = j.cfid WHERE j.jid = '$jid' LIMIT 1";
		$rs = $conn->createcommand($q)->queryOne(false);
		if($rs)
		{
		   $doc = $conn->createcommand("SELECT docattach FROM jobdocuments WHERE jid ='$jid' AND docid =1")->queryScalar();
		   $tbTr ="<table class='table table-bordered' >";
		   $tbTr .="<tr><td colspan=4 bgcolor='#0B67CD'><font color='#FFFFFF'><b>JOB DESCRIPTION</b></font></td></tr>";
		   $tbTr .="<tr><td>PART ONE:</td><td colspan=2>THIS JOB IS BEING HANDLED BY:&nbsp;<b>$rs[8]</b></td>";
		   $tbTr .="<td><b>Job Stage :&nbsp;<font color=blue>$rs[7]</font></b></td></tr>";
		   $tbTr .="<tr><td width=16%><b>&nbsp;Job ID:</b></td><td width=35%><b>$jid</b></td>";
		   $tbTr .="<td width=20%><b>&nbsp;Customer:</b></td><td>$rs[0]</td></tr>";
		   $tbTr .="<tr><td><b>&nbsp;BL #:</b></td><td>$rs[1]</td>";
		   $tbTr .="<td><b>&nbsp;Type Of Operation:</b></td><td>$rs[2]</td></tr>";
		   $tbTr .="<tr><td><b>&nbsp;BL Items:</b></td><td>$rs[3]</td>";
		   $tbTr .="<td><b>&nbsp;Quantity:</b></td><td>$rs[4]</td></tr>";
		   $tbTr .="<tr><td><b>&nbsp;Shipping Line:</b></td><td>$rs[5]</td>";
		   $tbTr .="<td><b>&nbsp;ICD:</b></td><td>$rs[6]</td></tr>";
		   //$tbTr .="<tr><td colspan=4><b>&nbsp;</td></tr>";
		  $q2 = "SELECT d.name,jd.docattach FROM jobdocuments jd INNER JOIN documents d ON d.docid = jd.docid WHERE jd.jid ='$jid' AND jd.docid IN(2,3,4,5)";
			$docs = $conn->createcommand($q2)->queryAll(false);
			foreach($docs as $d)
			{
				 $tbTr .="<tr><td colspan=2 align=right><b>$d[0]:&nbsp;</b></td>";
				 $tbTr .="<td colspan=2>&nbsp;<b>".Html::a('View',Yii::$app->request->baseUrl.'/cfdocs/'.$d[1],['target'=>'_blank'])."</b></td></tr>";

			}
		    //Other relevant documents
           $q3 = "SELECT d.name,jd.docattach,jd.id,jd.hascharges,jd.charges,jd.paidby,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),  ";
		   $q3 .= "DATE_FORMAT(jd.cdate,'%d/%m/%Y'),jd.inusd,jd.erate,jd.paidin,jd.hasvat,jd.vatamt,jd.vatinusd FROM jobdocuments jd  ";
		   $q3 .="INNER JOIN documents d ON d.docid = jd.docid INNER JOIN employees e ON e.empid = jd.cby WHERE jd.jid ='$jid' AND d.docid > 11";
		   $ord = $conn->createcommand($q3)->queryAll(false);
		    if(!empty($ord))
		    { 
			   $tbTr .="<tr><td colspan=4 bgcolor='#0B67CD'><font color='#FFFFFF'><b>EXPORT CERTIFICATE AND RELATED PERMITS</b></font></td></tr>";
			  
		      foreach($ord as $rd)
			  {
			    $tbTr .="<tr><td colspan=2>&nbsp;<b>".Html::a($rd[0],Yii::$app->request->baseUrl.'/cfdocs/'.$rd[1],['target'=>'_blank']).'&nbsp;|&nbsp;';
				$tbTr .= Html::a('<b>Remove</b>',['clearing/rmord','did'=>$rd[2],'jid'=>$jid,'type'=>'CERT'],['data'=>['confirm'=>'Are you sure you want to remove the selected Certificate?','method' => 'post',],])."&nbsp;&nbsp;|&nbsp;&nbsp;Uploaded by:&nbsp;</b>$rd[6]<br />&nbsp;<b>Date Uploaded:&nbsp;</b>$rd[7]</td>";
				if($rd[3] == 'Y')
				{
				  $det = "";
				  if($rd[11] == 'Y')
				  {
	$det = "&nbsp;VAT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(TZS):&nbsp;&nbsp;<b>".number_format($rd[12],2)."</b>&nbsp;In USD:&nbsp;<b>".number_format($rd[13],2)."</b><br />";  
				  }
				  $tbTr .= "<td colspan=2>&nbsp;Amount(TZS)&nbsp;:&nbsp;<b>".number_format($rd[4],2)."</b>&nbsp;In USD:&nbsp;<b>".number_format($rd[8],2)."</b>&nbsp;&nbsp;Exchange Rate:&nbsp;<b>".number_format($rd[9],2)."</b><br />$det&nbsp;Paid In:&nbsp;<b>$rd[10]</b>&nbsp;&nbsp;&nbsp;And Was Paid By:&nbsp;<b>$rd[5]</b></td>";
				}
				else
				{
					 $tbTr .= "<td><b>&nbsp;NO CHARGES</b></td><td>&nbsp;Paid By:&nbsp;<b>NA</b></td>";
				}
				$tbTr .="</tr>";  
			  } 
			  
		   }
		   
		   $qjid = "SELECT j.inumber,j.size,j.descr,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),j.blno FROM jobitems j INNER JOIN ";
		   $qjid .= "employees e ON e.empid = j.cby WHERE j.jid ='$jid'";
		   $rst  = $conn->createCommand($qjid)->queryAll(false);
		   
		$tbTr .="<tr bgcolor='#B34C00'><td colspan=2><font color='#FFFFFF'><b>Job Registered Details</b></font></td>";
		   $tbTr .="<td colspan=2><font color='#FFFFFF'><b>Packed List</b></font></td></tr>";
		   //$tbTr .="<td><font color='#FFFFFF'><b>Registered By</b></font></td></tr>";
		   $j = 1;
		   foreach($rst as $r)
		   {
			    $scode = Yii::$app->db->createCommand("SELECT DISTINCT scode FROM orderitems WHERE blno = '$r[4]'")->queryScalar();
				$tbTr .="<tr><td colspan=2><b>$j ]</b>&nbsp;$rs[3] #: <b><i>$r[0] - $r[1]</i></b></td>";
				$tbTr .="<td colspan=2>".Html::a('<i class="glyphicon glyphicon-print"></i> View',['operation/plist', 'scode' => $scode],['target'=>'_blank'])."</td></tr>";
				$j++;
		   }
		   
		    $q4 = "SELECT d.name,jd.docattach,jd.hascharges,jd.charges,jd.paidby,jd.id,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),  ";
		    $q4 .= "DATE_FORMAT(jd.cdate,'%d/%m/%Y'),jd.inusd,jd.erate,jd.paidin,jd.hasvat,jd.vatamt,jd.vatinusd FROM jobdocuments jd INNER JOIN documents d ON ";
		    $q4 .="d.docid = jd.docid INNER JOIN employees e ON e.empid = jd.cby WHERE jd.jid ='$jid' AND d.docid IN(6,7,8,9,10,11)";
		   $pcert = $conn->createcommand($q4)->queryAll(false);
		    if(!empty($pcert))
		    { 
			   $tbTr .="<tr><td colspan=4 bgcolor='#0B67CD'><font color='#FFFFFF'><b>AVAILABLE JOB CLEARING DOCUMENTS</b></font></td></tr>";
		      foreach($pcert as $cert)
			  {
			    $tbTr .= "<tr><td colspan=2><b>".Html::a($cert[0],Yii::$app->request->baseUrl.'/cfdocs/'.$cert[1],['target'=>'_blank']).'&nbsp;&nbsp;';
			$tbTr .= Html::a('<b>Remove</b>',['clearing/rmord','did'=>$cert[5],'jid'=>$jid,'type'=>'DOC'],['data'=>['confirm'=>'Are you sure you want to remove the selected Certificate?','method' => 'post',],])."</b>&nbsp;&nbsp;|&nbsp;&nbsp;<b>Uploaded by:&nbsp;</b>$cert[6]<br />&nbsp;<b>Date Uploaded:&nbsp;</b>$cert[7]</td>";
				if($cert[2] == 'Y')
				{
				  //$tbTr .= "<td>&nbsp;Amount:&nbsp;<b>".number_format($cert[3],2)."</b></td><td>&nbsp;Paid By:&nbsp;<b>$cert[4]</b></td>";
				  
				  $det2 = "";
				  if($cert[11] == 'Y')
				  {
	$det2 = "&nbsp;VAT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(TZS):&nbsp;&nbsp;<b>".number_format($cert[12],2)."</b>&nbsp;In USD:&nbsp;<b>".number_format($cert[13],2)."</b><br />";  
				  }
				  
				   $tbTr .= "<td colspan=2>&nbsp;Amount(TZS):&nbsp;<b>".number_format($cert[3],2)."</b>&nbsp;In USD:&nbsp;<b>".number_format($cert[8],2)."</b>&nbsp;&nbsp;&nbsp;Exchange Rate:&nbsp;<b>".number_format($cert[9],2)."</b><br />$det2&nbsp;Paid In:&nbsp;<b>$rd[10]</b>&nbsp;&nbsp;&nbsp;And Was Paid By:&nbsp;<b>$cert[4]</b></td>";
				}
				else
				{
					 $tbTr .= "<td><b>NO CHARGES</b></td><td>&nbsp;Paid By:&nbsp;<b>NA</b></td>";
				}
				$tbTr .="</tr>";  
			  } 
			  
		   }
		   
		   
		   

$tbTr .="<tr><td colspan=2 align=center>".Html::a('<i class="glyphicon glyphicon-file"></i> Add Required Import Certificate and Related Permits',['clearing/permits','jid'=>$jid],['class'=>'btn primary btn-xs'])."</td>";

          $cnt = $conn->createCommand("SELECT COUNT(*) FROM jobdocuments WHERE docid IN(6,7,8,9,10,11) AND jid ='$jid'")->queryScalar();
		  if($cnt < 6)
		  {
			 
			 $tbTr .="<td colspan=2 align=center>".Html::a('<i class="glyphicon glyphicon-list-alt"></i> Update Job Progress',['clearing/jobupdates','jid'=>$jid],['class'=>'btn green btn-xs'])."</td></tr>";
		  }
		  else
		  {
			$tbTr .= "<td colspan=2 align=center><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Done. Send to Invoice',['clearing/jobinv','jid'=>$jid],['data'=>['confirm'=>'When you send to Invoice you can not do any further changes on this job.Are you sure you want to send?','method' => 'post',],'class'=>'btn green btn-xs'])."</b></td></tr>";  
		  }
		  $tbTr .="</table>";
		}
		else
		{
			 $tbTr ="<table class='table table-bordered' >";
			  $tbTr .="<tr><td colspan=4 bgcolor='#7d98c2'><font color='#FFFFFF'><b>Invalid Request</b></font></td></tr></table>";
		}
		return $tbTr;
	}
	
	public function actionRmord($did,$jid,$type)
	{
		Yii::$app->db->createCommand("DELETE FROM jobdocuments WHERE id ='$did'")->execute();
		if($type == 'CERT')
		{
			Yii::$app->session->setFlash('jsuccess',"Certificate Removed Successful.");  
		}
		else
		{
			 Yii::app()->user->setFlash('jsuccess',"Document Removed Successfull.");  
		}
		return $this->redirect(['clearing/viewone','jid'=>$jid]);
	}
	
	public function actionJobinv($jid)
	{
		$id = Yii::$app->user->id;
		Yii::$app->db->createCommand("UPDATE jobs SET stid = 8,eby='$id',edate = CURDATE() WHERE jid ='$jid' AND stid !=9")->execute();
		Yii::$app->session->setFlash('comsuccess',"The Job has been Successful Processed For An Invoice To Be Raised.");  
		return $this->render('vwcomm');
		
		
	}
	
	public function getJID()
	{
		    $dt = date("Ym");
			$qj = "SELECT COUNT(jid) FROM jobs WHERE YEAR(cdate) = YEAR(CURDATE()) AND MONTH(cdate) = MONTH(CURDATE())";
			$num = Yii::$app->db->createCommand($qj)->queryScalar();
			if($num < 10)
			{
			  $jid = $dt.'-000'.($num + 1);
			}
			elseif($num < 100)
			{
			   $jid = $dt.'-00'.($num + 1);
			}
			elseif($num < 1000)
			{
			   $jid = $dt.'-0'.($num + 1);
			}
			else
			{
			    $jid = $dt.'-'.($num + 1);
			}
			return $jid;
	}
	
	
	////////////////////////////////////////////////////////////////////////
	
	public function actionClstep1()
	{
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		  if($this->hasNoItems())
		  {
			  return $this->render('vwnoitem');
		  }
		  
		    unset($_SESSION['slid']);
			unset($_SESSION['JobItems']);
			unset($_SESSION['containerno']);
			unset($_SESSION['expsdate']);
			unset($_SESSION['expardate']);
		$model = new Shipping;
		 if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		 {
			 $_SESSION['slid'] = $model->slid;
			 $_SESSION['containerno'] = $model->containerno;
			 $_SESSION['expsdate'] = $model->expsdate;
			 $_SESSION['expardate'] = $model->expardate;
			 return $this->redirect(['operation/loading']);
		 }
		 
		 return $this->render('_fcl',['model'=>$model]);
		
	}
	
	public function actionCntloading()
	{
	  
	  if($_SESSION['JobItems'])
		{	
          $id = Yii::$app->user->id;
		  $slid = $_SESSION['slid'];
		  $cntno = $_SESSION['containerno'];
		  $sdates = explode("/",$_SESSION['expsdate']);
		  $tdates = explode("/",$_SESSION['expardate']);
		  
		  $sdate = $sdates[2].'-'.$sdates[1].'-'.$sdates[0];
		  $tdate = $tdates[2].'-'.$tdates[1].'-'.$tdates[0];
		  
		  $num = Yii::$app->db->createCommand("SELECT shipcode FROM accode")->queryScalar();
		    Yii::$app->db->createCommand("UPDATE accode SET shipcode = shipcode + 1")->execute();
		    $scode = str_shuffle($this->getCode().$num);
		  foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
                    $q ="UPDATE orderitems SET slid ='$slid',scode='$scode',shipped ='Y',containerno ='$cntno',expsdate='$sdate',";
					$q .="expardate='$tdate',picked = 'Y',sby='$id',sdate=NOW() WHERE id='$ckey'";
				    Yii::$app->db->createCommand($q)->execute();
					  		
                }
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['slid']);  //remove ALL chassis number to the session variable
						unset($_SESSION['containerno']);
						unset($_SESSION['expsdate']);
						unset($_SESSION['expardate']);
						
			
			 Yii::$app->session->setFlash('osuccess','Customer Items Has Been Successfull Loaded Into Container');
			 $this->redirect(['operation/showitc','scode'=>$scode]);
		}
	}
	
	public function actionLoading()
	{
		
	if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $cid = $_SESSION['cid'];
		  $models = $this->getUNLItems();
		  $hasTotal = false;
		  
		 
		  if(isset($_POST['btnAdd'])) 
		  {  
			    foreach($models as $i=>$mod)
			    {
					
			      $mod[9]->attributes = $_POST['Shipping2'][$i];
				  if($mod[9]->picked == 1)
				   {
					  $_SESSION['JobItems'][$mod[9]->id]++; 
				   }
			    }
			
			  $models = $this->getUNLItems();
          }
		  
		   $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered table-gray'><thead><tr><td colspan=9><b>Items to be Shipped into this Container</b></td></tr></thead>";
		   $tbInvoice .= "<tr><th>SN</th><th>Option</th><th>Control No</th><th>Item Group</th><th>Items</th>";
		   $tbInvoice .="<th>CBM</th><th>Price</th><th>Client</th><th>Shipping As</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  
					  $q ="SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name FROM orderitems oi INNER JOIN itemgroup g";
                      $q .=" ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
			          $q .="WHERE oi.id = '$ckey'";
					  $pr = Yii::$app->db->createCommand($q)->queryOne(0);
					  if($pr[2] == 'Y')
					  {
						$cbm = $pr[3];
					  }
					  else
					  {
						 $cbm = 'NA'; 
					  }
					  
					  $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($pr[1]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					$it = rtrim($it,' , ');
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem6','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$pr[0]</td><td>$pr[7]</td><td>$it</td><td>$cbm</td><td>".number_format($pr[4],2)."</td>";
					 $tbInvoice .="<td>$pr[5]</td><td>$pr[6]</td></tr>";
				
					 $i++; 
				  }
			 $tbInvoice .="<tr><td colspan=9 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Load Selected Items Into Container',['operation/cntloading'],['data'=>['confirm'=>'Load Selected Items to this Container?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				 }
				 $tbInvoice .="</table>";
		return $this->render('_fclitems',['tbDet'=>$this->getContDet(),'models'=>$models,
		'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  	  
	}
	
	public function getContDet()
	{
		 $fdate = $_SESSION['expsdate'];
		 $tdate = $_SESSION['expardate'];
		 $cno = $_SESSION['containerno'];
		 $slid = $_SESSION['slid'];
		 $name = Yii::$app->db->createCommand("SELECT name from sline WHERE slid = '$slid'")->queryScalar();
		
		
		$tbData = "<table class='table table-bordered table-gray'><thead><tr>";
		$tbData .= "<th>SHIPPING LINE</th><th>CONTAINER NO</th><th>EXP. SHIPPING DATE</th><th>EXP. ARRIVAL DATE</th></thead></tr></thead>";
		
		if($name)
		{
			$tbData .="<tr><td>$name</td><td>$cno</td><td>$fdate</td><td>$tdate</td></tr></table>";
			return $tbData;
		}
		else
		{
			return $tbData .= "<tr><td><b>Invalid Request</td></tr></table>";
		}
		
		
	}
	
	public function getContainers()
	{
		 if($_SESSION['Cnt'])
		  {
		    foreach($_SESSION['Cnt'] as $ckey=>$val) 
			{
			  $ck = explode(":",$ckey);
			  $Aselected .=$ck[0].",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
			 
			  $q2 ="SELECT sl.name,oi.blno,oi.containerno,DATE_FORMAT(oi.expsdate,'%d/%m/%Y'),DATE_FORMAT(oi.expardate,'%d/%m/%Y'),";
		      $q2 .="COUNT(oi.iid) FROM orderitems oi INNER JOIN sline as sl ON sl.slid = oi.slid WHERE oi.shipped = 'Y' ";
			  $q2 .="AND oi.hasbl = 'Y' AND NOT oi.containerno IN('$RS') AND oi.jid IS NULL ";
			  $q2 .="GROUP BY sl.name,oi.blno,oi.containerno ORDER BY oi.expsdate DESC";
		    
		  }
		  else
		  {
			 $q2 ="SELECT sl.name,oi.blno,oi.containerno,DATE_FORMAT(oi.expsdate,'%d/%m/%Y'),DATE_FORMAT(oi.expardate,'%d/%m/%Y'),";
		     $q2 .="COUNT(oi.iid) FROM orderitems oi INNER JOIN sline as sl ON sl.slid = oi.slid WHERE oi.shipped = 'Y' ";
			 $q2 .="AND oi.hasbl = 'Y' AND oi.jid IS NULL GROUP BY sl.name,oi.blno,oi.containerno ORDER BY oi.expsdate DESC";
		  }	
		 
		$data = [];
		$rst = Yii::$app->db->createCommand($q2)->queryAll(false);
		foreach($rst as $rs)
		{
			
			
			$bsc = new Containers;
			$bsc->containerno = $rs[2];
			$rs[6] = $bsc;
			$data[] = $rs;
		}
		return $data;
	    
	}
	
	public function getUNLItems()
	{
		 if($_SESSION['JobItems'])
		  {
		    foreach($_SESSION['JobItems'] as $ckey=>$val) 
			{
			  $ck = explode(":",$ckey);
			  $Aselected .=$ck[0].",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
			 
			$q ="SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name,oi.id FROM orderitems oi INNER JOIN itemgroup g ";
            $q .="ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
			$q .="WHERE oi.shipped = 'N' AND oi.pcalc = 'NOW' AND NOT oi.id IN('$RS') ORDER BY c.name";
		    
		  }
		  else
		  {
			$q ="SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name,oi.id FROM orderitems oi INNER JOIN itemgroup g ";
            $q .="ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
			$q .="WHERE oi.shipped = 'N'  AND oi.pcalc = 'NOW' ORDER BY c.name";
		  }	
		 
		$data = [];
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		foreach($rst as $rs)
		{
			
			
			$bsc = new Shipping2;
			//$bsc->it = $it;
			$bsc->id = $rs[8];
			$rs[9] = $bsc;
			$data[] = $rs;
		}
		return $data;
	    
	}
	
	public function getUNLItems2()
	{
		 $scode = $_SESSION['scode'];
		 if($_SESSION['JobItems'])
		  {
		    foreach($_SESSION['JobItems'] as $ckey=>$val) 
			{
			  $ck = explode(":",$ckey);
			  $Aselected .=$ck[0].",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
			 
			$q ="SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name,oi.id FROM orderitems oi INNER JOIN itemgroup g ";
            $q .="ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
			$q .="WHERE oi.shipped = 'N' AND oi.pcalc = 'NOW' AND NOT oi.id IN('$RS') UNION ";
			$q .="SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name,oi.id FROM orderitems oi INNER JOIN itemgroup g ";
            $q .="ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
			$q .="WHERE oi.scode = '$scode' AND oi.pcalc = 'NOW' AND oi.picked = 'N' AND oi.hasbl = 'N' AND NOT oi.id IN('$RS') ";
		    
		  }
		  else
		  {
			$q ="SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name,oi.id FROM orderitems oi INNER JOIN itemgroup g ";
            $q .="ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
			$q .="WHERE oi.shipped = 'N'  AND oi.pcalc = 'NOW' UNION ";
			$q .="SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name,oi.id FROM orderitems oi INNER JOIN itemgroup g ";
            $q .="ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
			$q .="WHERE oi.scode = '$scode'  AND oi.pcalc = 'NOW' AND oi.picked = 'N' AND oi.hasbl = 'N'";
		  }	
		 
		$data = [];
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		foreach($rst as $rs)
		{
			
			
			$bsc = new Shipping2;
			//$bsc->it = $it;
			$bsc->id = $rs[8];
			$rs[9] = $bsc;
			$data[] = $rs;
		}
		return $data;
	    
	}
	
	
	
	/////////////////////END HERE//////////////////////////////////////
	
	public function hasNoItems()
	{
		$r = Yii::$app->db->createCommand("SELECT COUNT(*) FROM orderitems WHERE shipped = 'N' AND pcalc = 'NOW'")->queryScalar();
		if($r > 0)
		{
			return false;
		}
		return true;
	}
	/////////////////////////////
	
	public function actionNeworder()
	{
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		    unset($_SESSION['cid']);
			 unset($_SESSION['JobItems']);
			 unset($_SESSION['sas']);
			  unset($_SESSION['JobItems2']);
			unset($_SESSION['odate']);
		$model = new Orders;
		 if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		 {
			 $_SESSION['cid'] = $model->cid;
			 $_SESSION['sas'] = $model->sas;
			 $_SESSION['odate'] = $model->orderdate;
			 return $this->redirect(['operation/orders']);
		 }
		 
		 return $this->render('_forder',['model'=>$model]);
		
	}
	
	public function actionIedits1($ono)
	{
		unset($_SESSION['JobItems']);
		$rs = Yii::$app->db->createCommand("SELECT orderno,cid,sas,orderdate FROM orders WHERE orderno ='$ono'")->queryOne(0);
		$_SESSION['orderno'] = $rs[0];
		$_SESSION['cid'] = $rs[1];
		$_SESSION['sas'] = $rs[2];
		$_SESSION['odate'] = $rs[3];
		
		//Load Mzigo Kama Ulivyokuwa Mwanzo Kabla ya Kusave
		//$model->gid.':'.$model->cbm.':'.$model->cno.':'.$iid.':'.$model->pcalc
		$q ="SELECT DISTINCT g.gid,g.cper,oi.cbm,oi.cno,oi.iid,oi.pcalc FROM orderitems oi INNER JOIN itemgroup g ON ";
		$q .="g.gid = oi.gid WHERE oi.orderno = '$ono' AND oi.shipped = 'N'";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		 foreach($rst as $rs)
		 {
		  $_SESSION['JobItems'][$rs[0].':'.$rs[1].':'.$rs[2].':'.$rs[3].':'.$rs[4].':'.$rs[5]]++;
         }		  
		return $this->redirect(['operation/orders2']);
	}
	
	public function actionIedits2($ono,$id)
	{
		$rs = Yii::$app->db->createCommand("SELECT orderno,cid,sas,orderdate FROM orders WHERE orderno ='$ono'")->queryOne(0);
		$_SESSION['orderno'] = $rs[0];
		$_SESSION['cid'] = $rs[1];
		$_SESSION['sas'] = $rs[2];
		$_SESSION['odate'] = $rs[3];
		$cid = $rs[1];
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }

		  $model = new Squeeze3;
		  $hasTotal = false;
		  $hasItems2 = false;

		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				//add this item to the session variable 
                $_SESSION['JobItems'][$model->iid.':'.$model->cbm]++;  
			    $this->refresh(); 
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Control No</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>CBM</th><th>Calculation</th><th>Price (USD)</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  $Total = 0;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ml = explode(':',$ckey);
					  $qi = "SELECT g.name,g.rate,oi.iid,g.cper,oi.cno FROM orderitems oi INNER JOIN ";
                      $qi .="itemgroup g ON g.gid = oi.gid WHERE oi.id = '$ml[0]'";					  
					  $ck = Yii::$app->db->createCommand($qi)->queryOne(0);
					  
					   if($ck[3] == 'CBM')
					  {
						$price = $ml[2] * $ck[1];  
						$cbm = $ml[2];
						$calc = $ml[2]. ' X '. $ck[1];
					  }
					  else
					  {
						 $price = $ck[1];  
						 $calc = '1 X '. $ck[1];
						 $cbm = 'NA'; 
					  }
					  
					  $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[2]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $it = rtrim($it,' , ');
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Edit</b>",['operation/rmitemupdate','cid'=>$ckey,'ono'=>$ono,'id'=>$id])."</b></td><td>$ck[4]</td>";
		             $tbInvoice .="<td>$ck[0]</td><td>$it</td><td>$cbm</td><td>$calc<td><b>".number_format($price,2)."</b></td><tr>";
				
					 $i++; 
				  }
				  $tbInvoice .="<tr><td colspan=8 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Fine.Update Items Cost ',['operation/updatecost','id'=>$id],['data'=>['confirm'=>'Submit And Save?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
			// $tbInvoice .="<tr><td colspan=7 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 
				 $tbInvoice .="</table>";
		         return $this->render('_fuitems',['model'=>$model,'tbDet'=>$this->getCDet($cid),
		                               'tbInv'=>$tbInvoice,'hasItems'=>$hasItems,'id'=>$id]);
		  
		
	}
	
	public function actionUpdatecost($id)
	{
		
		 foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ml = explode(':',$ckey);
					  $qi = "SELECT g.name,g.rate,oi.iid,g.cper,oi.cno FROM orderitems oi INNER JOIN ";
                      $qi .="itemgroup g ON g.gid = oi.gid WHERE oi.id = '$id'";					  
					  $ck = Yii::$app->db->createCommand($qi)->queryOne(0);
					  
					   if($ck[3] == 'CBM')
					  {
						$price = $ml[2] * $ck[1];  
						$cbm = $ml[2];
						$hascbm = 'Y';
					  }
					  else
					  {
						 $price = $ck[1];  
						 $hascbm = 'N';
						 $cbm = 0; 
					  }
					  
					  $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[2]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $it = rtrim($it,' , ');
					 
				     $qu ="UPDATE orderitems SET pcalc ='NOW',hascbm = '$hascbm',cbm = '$cbm', price = '$price', pricesby = '$id',";
					 $qu .="pricesdate = NOW() WHERE id = '$id'";
					 Yii::$app->db->createCommand($qu)->execute();
					 $price2 = number_format($price,2);
					 $msg = "Received Cargo With Items [$it] And Control #: <b>$ck[4]</b> Charges Has been set to #: <b>$price2</b>";
				     LogisticsRoles::logAction('Cargo Handling',$msg);
				  }
				  Yii::$app->session->setFlash('asuccess','Cargo Price Has Been Successful Saved');
			 $this->redirect(['operation/nsorders']);
		
	}
	
	public function actionOrders2()
	{
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $cid = $_SESSION['cid'];
		  $model = new Orderitems;
		  $hasTotal = false;
		  
		  if(isset($_POST['btnGeneral'])) 
		  {
			$orderno = $_SESSION['orderno'];
			
		     $Total = 0;
			 $id = Yii::$app->user->id;
		  if($_SESSION['JobItems'])
		   {	
		       //Put unedited item and then
			   //Remove all the items from orderitems that have not been shipped so that we can load again
			   $qi = "INSERT INTO orderedited(gid,iid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate,eby,edate) SELECT ";
			   $qi .= "gid,iid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate,'$id',NOW() FROM orderitems ";
			   $qi .= "WHERE orderno = '$orderno' AND shipped = 'N'";
			   Yii::$app->db->createCommand($qi)->execute();
               Yii::$app->db->createCommand("DELETE FROM orderitems WHERE orderno = '$orderno' AND shipped = 'N'")->execute();
			   foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				      $ck = explode(":",$ckey);
				     $pr = Yii::$app->db->createCommand("SELECT rate FROM itemgroup WHERE gid ='$ck[0]'")->queryScalar();
					 if($ck[5] == 'NOW')
					 {
						  if($ck[1] == 'CBM')
						  {
							$price = $ck[2] * $pr;  
							$cbm = $ck[2];
							$hascbm = 'Y';
						  }
						  else
						  {
							 $price = $pr;  
							 $hascbm = 'N';
							 $cbm = '0'; 
						  }
					 }
                     else
					 {
						$price = 0;  
						$hascbm = 'N';
						$cbm = '0';  
						 
					 }						 
					  
		                $qhc ="INSERT INTO orderitems(gid,iid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate)";
                        $qhc .=" VALUES('$ck[0]','$ck[4]','$orderno','$ck[3]','$hascbm','$cbm','$ck[5]','$price','$id',NOW())";
		                Yii::$app->db->createCommand($qhc)->execute();
						
					 
						
		        }

		             $msg = "Updted Cargo Items From Order #: <b>$orderno</b>";
				     LogisticsRoles::logAction('Cargo Handling',$msg);
					 
					  
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['cid']);
						unset($_SESSION['sas']);
						unset($_SESSION['odate']);
			
			 Yii::$app->session->setFlash('osuccess','Customer Cargo Items to Deliver has been Updated Successful');
			 $this->redirect(['operation/showorder','oid'=>$orderno]);
		               
	          }
	
			 
							 
		  }
		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				//add this item to the session variable 
				$iid = implode(',',$model->iid);
                $_SESSION['JobItems'][$model->gid.':'.$model->cbm.':'.$model->cno.':'.$iid.':'.$model->pcalc]++;  
			    $this->refresh(); 
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Control No</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>CBM</th><th>Calculation</th><th>Price (USD)</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $pr = Yii::$app->db->createCommand("SELECT name,rate FROM itemgroup WHERE gid ='$ck[0]'")->queryOne(0);
					  if($ck[5] == 'NOW')
					  {
					  if($ck[1] == 'CBM')
					  {
						$price = $ck[2] * $pr[1];  
						$cbm = $ck[2];
						$calc = $ck[2]. ' X '. $pr[1];
					  }
					  else
					  {
						 $price = $pr[1];  
						 $calc = '1 X '. $pr[1];
						 $cbm = 'NA'; 
					  }
					  
					  $Total = $Total + $price;
					  $price2 = number_format($price,2);
					  }
					  else
					  {
						$cbm = 'NA'; 
						$price2 = 'Later'; 
                        if($ck[1] == 'CBM')
					    { 
						$cbm = $ck[2];
					   }						
					  }
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem2','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$ck[3]</td><td>$pr[0]</td><td>";
					 $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[4]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $tbInvoice .= rtrim($it,' , ');
					 $tbInvoice .="</td><td>$cbm</td><td>$calc<td>$price2</td><tr>";
				
					 $i++; 
				  }
			 $tbInvoice .="<tr><td colspan=7 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 $tbInvoice .="</table>";
		return $this->render('_foitems2',['model'=>$model,'tbDet'=>$this->getCDet($cid),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
	}
	
	
	public function actionOrders()
	{
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $cid = $_SESSION['cid'];
		  $model = new Orderitems;
		  $hasTotal = false;
		  
		  if(isset($_POST['btnGeneral'])) 
		  {
			$code = 'S';
			$num = Yii::$app->db->createCommand("SELECT COUNT(*) FROM orders WHERE YEAR(cdate) = YEAR(CURDATE())")->queryScalar();
			if($num < 10)
			{
			  $orderno = $code.'000'.($num + 1).date('y');
			}
			elseif($num < 100)
			{
			   $orderno = $code.'00'.($num + 1).date('y');
			}
			elseif($num < 1000)
			{
				$orderno = $code.'0'.($num + 1).date('y');
			}
			else
			{
			    $orderno = $code.($num + 1).date('y');
			}
			
		     $Total = 0;
			 $id = Yii::$app->user->id;
		  if($_SESSION['JobItems'])
		   {	
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				      $ck = explode(":",$ckey);
				     $pr = Yii::$app->db->createCommand("SELECT rate FROM itemgroup WHERE gid ='$ck[0]'")->queryScalar();
					 if($ck[5] == 'NOW')
					 {
						  if($ck[1] == 'CBM')
						  {
							$price = $ck[2] * $pr;  
							$cbm = $ck[2];
							$hascbm = 'Y';
						  }
						  else
						  {
							 $price = $pr;  
							 $hascbm = 'N';
							 $cbm = '0'; 
						  }
					 }
                     else
					 {
						$price = 0;  
						$hascbm = 'N';
						$cbm = '0';  
						 
					 }						 
					  
		                $qhc ="INSERT INTO orderitems(gid,iid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate)";
                        $qhc .=" VALUES('$ck[0]','$ck[4]','$orderno','$ck[3]','$hascbm','$cbm','$ck[5]','$price','$id',NOW())";
		                Yii::$app->db->createCommand($qhc)->execute();
						
					  $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[4]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
					  }
					  $dt .= rtrim($it,' , ');
						$msg = "Received Cargo With Items [$dt] Which Has Been Given Control #: <b>$ck[3]</b>";
				        LogisticsRoles::logAction('Cargo Handling',$msg);
		        }

		             
					 $odates = explode("/",$_SESSION['odate']);
	                 $odate = $odates[2]."-".$odates[1]."-".$odates[0];
		             
					 $sas = $_SESSION['sas'];
					 $qinv ="INSERT INTO orders(orderno,cid,sas,orderdate,ostatus,cby,cdate) "; 
                     $qinv .="VALUES('$orderno','$cid','$sas','$odate','R','$id',NOW())";
                    
		              Yii::$app->db->createCommand($qinv)->execute();
					  
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['cid']);
						unset($_SESSION['sas']);
						unset($_SESSION['odate']);
			
			 Yii::$app->session->setFlash('osuccess','Customer Items to Deliver has been successful recorded');
			 $this->redirect(['operation/showorder','oid'=>$orderno]);
		               
	          }
	
			 
							 
		  }
		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				//add this item to the session variable 
				$iid = implode(',',$model->iid);
                $_SESSION['JobItems'][$model->gid.':'.$model->cbm.':'.$model->cno.':'.$iid.':'.$model->pcalc]++;  
			    $this->refresh(); 
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Control No</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>CBM</th><th>Calculation</th><th>Price (USD)</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $pr = Yii::$app->db->createCommand("SELECT name,rate FROM itemgroup WHERE gid ='$ck[0]'")->queryOne(0);
					  if($ck[5] == 'NOW')
					  {
					  if($ck[1] == 'CBM')
					  {
						$price = $ck[2] * $pr[1];  
						$cbm = $ck[2];
						$calc = $ck[2]. ' X '. $pr[1];
					  }
					  else
					  {
						 $price = $pr[1];  
						 $calc = '1 X '. $pr[1];
						 $cbm = 'NA'; 
					  }
					  
					  $Total = $Total + $price;
					  $price2 = number_format($price,2);
					  }
					  else
					  {
						$cbm = 'NA'; 
						$price2 = 'Later'; 
                        if($ck[1] == 'CBM')
					    { 
						$cbm = $ck[2];
					   }						
					  }
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$ck[3]</td><td>$pr[0]</td><td>";
					 $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[4]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $tbInvoice .= rtrim($it,' , ');
					 $tbInvoice .="</td><td>$cbm</td><td>$calc<td>$price2</td><tr>";
				
					 $i++; 
				  }
			 $tbInvoice .="<tr><td colspan=7 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 $tbInvoice .="</table>";
		return $this->render('_foitems',['model'=>$model,'tbDet'=>$this->getCDet($cid),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
	}
	
	public function actionGetsitems()
	{

         if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $pid = $parents[0];
			   $exp = explode(":",$pid);
			   
			   $q = "SELECT iid as id,name FROM items  WHERE gid ='$exp[0]' ORDER BY name";
           	   $data = Yii::$app->db->createCommand($q)->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
	}
	
	public function getItems()
	{
		 if($_SESSION['JobItems'])
		  {
		    foreach($_SESSION['JobItems'] as $ckey=>$val) 
			{
			  $ck = explode(":",$ckey);
			  $Aselected .=$ck[0].",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
			 $q ="SELECT itemid,name FROM items ORDER BY name";
		    // $q ="SELECT itemid,name FROM items WHERE NOT itemid IN('$RS') ORDER BY name";
		  }
		  else
		  {
			$q ="SELECT itemid,name FROM items ORDER BY name";
		  }	
		  $data = [];
		$rslt = Yii::$app->db->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	      $data[$rs[0]] = $rs[1];
	     }
		 return $data;
	}
	
	public function getItemsP()
	{
		 if($_SESSION['JobItems'])
		  {
		    foreach($_SESSION['JobItems'] as $ckey=>$val) 
			{
			  $ck = explode(":",$ckey);
			  $Aselected .=$ck[0].",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
			 $q ="SELECT CONCAT(itemid,':',prodid),name FROM products ORDER BY name";
		    // $q ="SELECT itemid,name FROM items WHERE NOT itemid IN('$RS') ORDER BY name";
		  }
		  else
		  {
			$q ="SELECT CONCAT(itemid,':',prodid),name FROM products ORDER BY name";
		  }	
		  $data = [];
		$rslt = Yii::$app->db->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	      $data[$rs[0]] = $rs[1];
	     }
		 return $data;
	}
	
	public function actionRmitem($cid)
	{
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['orders']);
	}
	
	public function actionRmitemsq($cid,$oid)
	{
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['squeeze','oid'=>$oid]);
	}
	
	public function actionRmcnt($cid)
	{
	   unset($_SESSION['Cnt'][$cid]);
	    return $this->redirect(['newjob']);
	}
	
	public function actionRmitemupdate($cid,$ono,$id)
	{
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['iedits2','ono'=>$ono,'id'=>$id]);
	}
	
	public function actionRmitem3($cid,$oid)
	{
	    unset($_SESSION['JobItems2'][$cid]);
	    return $this->redirect(['squeeze','oid'=>$oid]);
	}
	public function actionRmjb($cid)
	{
	    unset($_SESSION['Job'][$cid]);
	    return $this->redirect(['newjob']);
	}
	
	public function actionRmitem6($cid)
	{
	    unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['loading']);
	}
	public function actionRmitem7($cid)
	{
		Yii::$app->db->createCommand("UPDATE orderitems SET picked = 'N' WHERE id = '$cid'")->execute();
		unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['eloading']);
	}
	
	public function actionRmitem2($cid)
	{
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['orders2']);
	}
	
	public function getCDet($cid,$oid ='',$fsq ='')
	{
		$odate = $_SESSION['odate'];
		$sas = $_SESSION['sas'];
		if(empty($sas))
		{
			$sas = 'Same Name';
		}
		$q = "SELECT name,address,CONCAT('+',pcode,phone),ctype FROM clients WHERE cid = '$cid'";
		if($fsq == 'Y')
		{
			$odate = Yii::$app->db->createCommand("SELECT DATE_FORMAT(orderdate,'%d/%m/%Y') FROM orders WHERE cid ='$cid' AND orderno ='$oid'")->queryScalar();
		}
		$rs = Yii::$app->db->createCommand($q)->queryOne(0);
		
		$tbData = "<table class='table table-bordered table-gray'><thead><tr><th>CUSTOMER NAME</th><th>SHIPPING AS</th><th>ADDRESS</th>";
		$tbData .= "<th>PHONE</th><th>CUSTOMER TYPE</th><th>ORDER DATE</th></thead></tr></thead>";
		
		if($rs)
		{
			$tbData .="<tr><td>$rs[0]</td><td>$sas</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$odate</td></tr></table>";
			return $tbData;
		}
		else
		{
			return $tbData .= "<tr><td><b>Invalid Request</td></tr></table>";
		}
		
		
	}
	
	public function actionCorder($oid)
	{
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		$st = Yii::$app->db->createCommand("SELECT ostatus FROM orders WHERE orderno =:oid")->bindParam(':oid',$oid)->queryScalar();
		if($st != 'O')
		{
			 Yii::$app->session->setFlash('nsuccess','You can not Cancel Addendum That has Already Been Delivered/Cancelled.');
			 $this->redirect(['operation/addendum']);
		}
		
		$model = $this->loadOrder($oid);
		
		if($model->load(Yii::$app->request->post()) && $model->save())
		{
				Yii::$app->session->setFlash('asuccess','Addendum Has Been Successful Cancelled.');
				$this->redirect(['operation/addendum']);
		}

		return $this->render('_fcaddendum',['model'=>$model,'tbData'=>$this->getOrder($oid)]);
		
	}
	
	public function actionShoworder($oid)
	{
		return $this->render('vwo',['tbData'=>$this->getOrder($oid)]);
	}
	
	public function actionShowordersq($oid,$ncno)
	{
		return $this->render('vwo',['tbData'=>$this->getOrderSQ($oid,$ncno)]);
	}
	
	public function getOrderSQ($oid,$ncno)
	{
		
	    $qs = "SELECT c.name,c.address,c.paddress,CONCAT('+',c.pcode,c.phone),c.pcode2,c.phone2,c.email,c.ctype,";
		$qs .="DATE_FORMAT(o.orderdate,'%d/%m/%Y') FROM orders o INNER JOIN clients c ON c.cid = o.cid ";
		$qs .="WHERE o.orderno =:oid";
		
		$dt = Yii::$app->db->createCommand($qs)->bindParam(':oid',$oid)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table class='table table-bordered table-gray'><tr><td colspan=2><b>Order # : </b>$oid &nbsp;&nbsp;&nbsp;";
			 $tbH .="<b>Order DATE : </b>$dt[8]</td></tr>";
			 $tbH .="<thead><tr><th colspan=2>CLIENT DETAILS DETAILS</th></tr></thead>";
			 $tbH .="<tr><th width=20%>Client Name</th><td>$dt[0]</td></tr>";
		     $tbH .="<tr><th>Postal Address</th><td>$dt[1]</td></tr>";
			 $tbH .="<tr><th>Physical Address</th><td>$dt[2]</td></tr>";
			 $tbH .="<tr><th>Phone Number</th><td>$dt[3]</td></tr>";
			 if(!empty($dt[4]))
			 {
				  $tbH .="<tr><th>Phone Number</th><td>+$dt[4]$dt[5]</td></tr>";
			 }
			 $tbH .="<tr><th>Email</th><td>$dt[6]</td></tr>";
			 $tbH .="<tr><th>Client Type</th><td>$dt[7]</td></tr></table>";
		
		  $qo = "SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,g.rate,g.name FROM orderitems oi INNER JOIN itemgroup g ON ";
		  $qo .="g.gid = oi.gid WHERE oi.cno =:ncno ";
		  
		  $orders = Yii::$app->db->createCommand($qo)->bindParam(':ncno',$ncno)->queryAll(false);
	
		if(!empty($orders))
		{ 
	        $i = 1;
			$Total = 0;
			
			$tbO .="<table class='table table-bordered table-gray footable'><thead><tr><th colspan=7><b>AFTER SQUEEZED/REPACKED DESCRIPTIONS</b></th></tr>";
			//$tbO .="<td colspan=4>&nbsp;</td><th><b>".Html::a('Edit',['operation/eorder','oid'=>$oid])."</b></th></tr>";
		    $tbO .="<tr><th>SN</th><th>Control Number</th><th>Item Group</th><th>Items</th><th>CBM</th><th>Calculation</th>";
		    $tbO .="<th>Price (USD)</th></tr></thead>";
		     foreach($orders as $order)
			  {
			     if($order[2] == 'Y')
					  {
						$price = $order[3] * $order[5];  
						$cbm = $order[3];
						$calc = $order[3]. ' X '. $order[5];
					  }
					  else
					  {
						 $price = $order[5];  
						 $calc = '1 X '. $order[5];
						 $cbm = 'NA'; 
					  }
					  
				 $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($order[1]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					$it = rtrim($it,' , ');
					
					  $Total = $Total + $price;
				$tbO .="<tr><td>$i</td><td>$order[0]</td><td>$order[6]</td><td>$it</td><td><b>$cbm</b></td><td>$calc</td>";
				$tbO .="<td><b>".number_format($price,2)."</b></td></tr>";
				$i++;
			  }
			   $tbO .=" <tfoot><tr><td colspan=6 align=right><b><i>Total:</i></b></td><th>".number_format($Total,2)."</th></tr>";
			   $tbO .="</tfoot></table>";
		  }
		 }  
		return $tbH.$tbO;
		
	}
	
	public function getOrder($oid)
	{
		
	    $qs = "SELECT c.name,o.sas,c.address,c.paddress,CONCAT('+',c.pcode,c.phone),c.pcode2,c.phone2,c.email,c.ctype,";
		$qs .="DATE_FORMAT(o.orderdate,'%d/%m/%Y') FROM orders o INNER JOIN clients c ON c.cid = o.cid ";
		$qs .="WHERE o.orderno =:oid";
		
		$dt = Yii::$app->db->createCommand($qs)->bindParam(':oid',$oid)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table class='table table-bordered table-gray'><tr><td colspan=2><b>Order # : </b>$oid &nbsp;&nbsp;&nbsp;";
			 $tbH .="<b>Order DATE : </b>$dt[8]</td></tr>";
			 $tbH .="<thead><tr><th colspan=2>CLIENT DETAILS DETAILS</th></tr></thead>";
			 $tbH .="<tr><th width=20%>Client Name</th><td>$dt[0]</td></tr>";
			 $tbH .="<tr><th>Shipping As</th><td>$dt[1]</td></tr>";
		     $tbH .="<tr><th>Postal Address</th><td>$dt[2]</td></tr>";
			 $tbH .="<tr><th>Physical Address</th><td>$dt[3]</td></tr>";
			 $tbH .="<tr><th>Phone Number</th><td>$dt[4]</td></tr>";
			 if(!empty($dt[5]))
			 {
				  $tbH .="<tr><th>Phone Number</th><td>+$dt[5]$dt[6]</td></tr>";
			 }
			 $tbH .="<tr><th>Email</th><td>$dt[7]</td></tr>";
			 $tbH .="<tr><th>Client Type</th><td>$dt[8]</td></tr></table>";
		
		  $qo = "SELECT oi.cno,g.name,oi.iid,oi.hascbm,oi.cbm,oi.pcalc,oi.price,g.rate FROM orderitems oi INNER JOIN ";
		  $qo .="itemgroup g ON g.gid = oi.gid WHERE oi.orderno =:oid ";
		  
		  $orders = Yii::$app->db->createCommand($qo)->bindParam(':oid',$oid)->queryAll(false);
	
		if(!empty($orders))
		{ 
	        $i = 1;
			$Total = 0;
			
			$tbO .="<table class='table table-bordered table-gray footable'><thead><tr><th colspan=7><b>ITEMS DESCRIPTIONS</b></th></tr>";
		    $tbO .="<tr><th>SN</th><th>Control Number</th><th>Item Group</th><th>Items</th><th>CBM</th><th>Calculation</th>";
		    $tbO .="<th>Price (USD)</th></tr></thead>";
		     foreach($orders as $order)
			  {
			     
				 $tbO .="<tr><td>$i</td><td>$order[0]</td><td>$order[1]</td><td>";
				 $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($order[2]) ORDER BY name")->queryAll(false);
					  $dt = '';
					  foreach($rst as $rs)
					  {
						  $dt .= $rs[0].' , ';
						
					  }
					  $d = rtrim($dt,' ,');
					  $tbO .= $d;
				if($order[5] == 'NOW')
				{					
					 if($order[3] == 'Y')
						  {
							$price = $order[6];  
							$cbm = $order[4];
							$calc = $order[4]. ' X '. $order[7];
						  }
						  else
						  {
							 $price = $order[6];  
							 $calc = '1 X '. $order[7];
							 $cbm = 'NA'; 
						  }
						  
						  $Total = $Total + $price;
						  $price2 = number_format($price,2);
				}
				else
				{
					if($order[3] == 'Y')
						  {
							$price2 = 'Later'; //$order[3] * $order[5];  
							$cbm = $order[4];
							$calc = 'Later'; //$order[3]. ' X '. $order[5];
						  }
						  else
						  {
							 $price2 = 'Later'; //$order[5];  
							 $calc = 'Later'; //'1 X '. $order[5];
							 $cbm = 'NA'; 
						  }
						  
				}
				
				$tbO .="</td><td><b>$cbm</b></td><td>$calc</td><td><b>$price2</b></td></tr>";
				$i++;
			  }
			   $tbO .=" <tfoot><tr><td colspan=6 align=right><b><i>Total:</i></b></td><th>".number_format($Total,2)."</th></tr>";
			   $tbO .="</tfoot></table>";
		  }
		 }  
		return $tbH.$tbO;
		
	}
	
	public function actionEorder($oid,$pid,$wt)
	{
		  
		  if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  
		  $qo = "SELECT p.name,o.weight,o.munit,o.price,COUNT(o.prodid) FROM orderitems o ";
		  $qo .="INNER JOIN products p ON p.prodid = o.prodid WHERE o.orderno ='$oid' AND o.prodid ='$pid' AND o.weight = '$wt' ";
          $qo .="GROUP BY o.prodid,o.weight";
		  $d = Yii::$app->db->createCommand($qo)->queryOne(0);
		  
		  $model = new Uorder;
		  $model->prodid = $d[0];
		  $model->quantity = $d[4];
		  $model->weight = $d[1];
		  $model->munit = $d[2];
		  
		if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		  $id = Yii::$app->user->id;
		  
		  $q = "SELECT COUNT(o.prodid) FROM orderitems o WHERE o.orderno ='$oid' AND o.prodid ='$pid' AND o.weight = '$wt'";
		  $idadi = Yii::$app->db->createCommand($q)->queryScalar();
		  $it = Yii::$app->db->createCommand("SELECT itemid,price,eddate FROM orderitems WHERE orderno ='$oid' AND prodid ='$pid' AND weight = '$wt' LIMIT 1")->queryOne(0);
		  $cnt = $model->quantity;
		  $diff = $cnt - $idadi;
		    if($diff > 0)
		     {	  
		        for($i = 1; $i <= $diff; $i++)
		         {			 
		               $qhc ="INSERT INTO orderitems(itemid,prodid,orderno,weight,munit,price,eddate,status,cby,cdate)";
                        $qhc .=" VALUES('$it[0]','$pid','$oid','$d[1]','$d[2]','$it[1]','$it[2]','O','$id',NOW())";
		                Yii::$app->db->createCommand($qhc)->execute();
		         }
				 Yii::$app->session->setFlash('rsuccess',"Ordered Units Goods Successful Updated.");
		     }
			 else
			 {
				 $diff = $idadi - $cnt;
				 $num = 0;
				  if($diff > 0)
		           {	  
		             for($i = 1; $i <= $diff; $i++)
		              {	
                        $id2 = Yii::$app->db->createCommand("SELECT id FROM orderitems WHERE orderno ='$oid' AND prodid ='$pid' AND weight = '$wt' AND status = 'O' LIMIT 1")->queryScalar();
						
		                Yii::$app->db->createCommand("DELETE FROM orderitems WHERE id = '$id2'")->execute();
						$num = $num + 1;
		               
		             }
				   Yii::$app->session->setFlash('rsuccess',"$num Ordered Units Has Been Removed From Previous Ordered Units.");
		         }
			 }
		  $model->prodid ='';
		  $model->quantity ='';
		  $model->weight ='';
		   $model->munit ='';
		   
        }
		
        return $this->render('_fuorders',['model'=>$model,'tbData'=>$this->getIDetails2($oid)]);
	}
	
	public function getIDetails2($oid)
	{

		$orderno = $_SESSION['orderno'];
		$qs = "SELECT s.name,o.orderno,DATE_FORMAT(o.orderdate,'%d/%m/%Y'),DATE_FORMAT(o.expddate,'%d/%m/%Y') FROM orders o ";
		$qs .="INNER JOIN suppliers s ON s.supid = o.supid WHERE o.orderno = '$oid'";
		
		$dt = Yii::$app->db->createCommand($qs)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table class='table table-bordered table-gray'>";
			 $tbH .="<thead><tr><th>SUPPLIER</th><th>ORDER NO</th><th>ORDER DATE</th><th>EXPECTED DELIVERY DATE</th></tr></thead>";
			 $tbH .="<tr><td><b>$dt[0]</b></td><td><b>$dt[1]</b></td><td><b>$dt[2]</b></td><td><b>$dt[3]</b></td></tr></table>";
			 
			 $qd = "SELECT p.name,o.weight,o.munit,o.prodid,COUNT(o.id) FROM orderitems o ";
			 $qd .="INNER JOIN products p ON p.prodid = o.prodid WHERE o.orderno ='$oid' GROUP BY ";
			 $qd .="p.name,o.weight,o.munit,o.prodid";
			 
			 $rst = Yii::$app->db->createCommand($qd)->queryAll(false);
			  if(!empty($rst))
			  {
				  $i = 1;
			$tbD ="<table class='table table-bordered'><tr><td><b>Sn</b></td><td><b>Product Name</b></td><td><b>Weight</b></td>";
				  $tbD .="<td><b>Ordered Unit</b></td><td><b>Action</b></td></tr>";
				  $tt = 0;
				  foreach($rst as $rs)
				  {
					$tt = $tt + $rs[4];
					$tbD .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]$rs[2]</td><td>$rs[4]</td>";
					$tbD .="<td><b>".Html::a('Edit',['operation/eorder','oid'=>$oid,'pid'=>$rs[3],'wt'=>$rs[1]])."</b></td></tr>"; 
                     $i++;					
				  }
				 $tbD .="<tr><td colspan=3 align=right><b><i>Total:</i></b></td><td><b>".number_format($tt)."</b></td><td>&nbsp;</td></tr></table>"; 
			  }
			  
			
			
		  }
		return $tbH.$tbD;
		
	}
	
	public function actionAddendum()
    {
        
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		
          $q2 ="SELECT @s:=@s+1 as Sn, s.name as Supplier,o.orderno as 'Order No',DATE_FORMAT(o.orderdate,'%d/%m/%Y') as 'Order Date',";
		  $q2 .="DATE_FORMAT(o.exppdate,'%d/%m/%Y') as 'Production Date',DATE_FORMAT(o.expddate,'%d/%m/%Y') as 'Delivery Date',";
		  $q2 .="CASE o.ostatus WHEN 'O' THEN 'Not Yet Delivered' WHEN 'D' THEN 'Delivered' ELSE 'Cancelled' END as 'Order Status' ";
		  $q2 .="FROM orders o INNER JOIN suppliers s On s.supid = o.supid,(SELECT @s:=0) AS s ORDER BY o.expddate DESC";
		
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM orders")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Supplier','Order No','Registered Date','Delivery Date'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);	
      
         	  
		return $this->render('addendums',['dataProvider'=>$dataProvider]);
    }
	
	
	
	public function actionPayments()
    {
        
		
		if(!FireRoles::isAccount())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		
		$model = new Receipts;
		$isValid = false;
		if(isset($_POST['btnReceipt'])) 
		  {  
	        $isValid = true;
	       
		  if($model->load(Yii::$app->request->post()) && $model->save())
		   {
				Yii::$app->session->setFlash('rsuccess',"Payments Recorded Successful.Please Click Link Below to Print Receipt");
			    $tbTr ="<table class='table table-striped table-bordered'><tr><th>&nbsp;</th></tr>";
		  $tbTr .="<tr><td>&nbsp;".Html::a("<b>Print Receipt</b>",['management/printrct','rid'=>$model->receiptno],['target'=>'_blank'])."</td></tr>";
			    $tbTr .="</table>";
				return $this->render('showrct',['tbRct'=>$tbTr]);
		   }
		   $model->ino2 = $model->invoicenum;
				$model->amount = Yii::$app->db->createCommand("SELECT FORMAT(tamount,2) FROM invoice WHERE invoicenum = '$model->ino2'")->queryScalar();
          }
		  
		  if(isset($_POST['btnSearch'])) 
		  { 
             $model->payopt = 'CASH';	  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				 
				$model->ino2 = $model->invoicenum;
				$model->amount = Yii::$app->db->createCommand("SELECT FORMAT(tamount,2) FROM invoice WHERE invoicenum = '$model->ino2'")->queryScalar();
				$model->payopt = '';
				$isValid = true;
		   }
          }
		
         	  
		return $this->render('payments',['model'=>$model,'isValid'=>$isValid]);
    }
	
	public function actionAdmin()
	{
		return $this->render('admin');
	}
	public function actionCertificate()
    {
        
		if(!FireRoles::isCert())
		  {
			  return $this->redirect(['management/noaccess']);
		  }

	    $q2 ="SELECT @s:=@s+1 as Sn,CONCAT(fname,' ',IFNULL(mname,''),' ',sname) as Client,telno as Telephone,pshno as House,";
		$q2 .="paddress as Address,cdate as 'Registered Date',applid FROM application,(SELECT @s:=0) AS s WHERE printed = 'N' ";
		$q2 .="AND status ='STAGE6' ORDER BY cdate";
		
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM application WHERE printed = 'N'")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Client','Registered Date'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);	
      
         	  
		return $this->render('cert',['dataProvider'=>$dataProvider]);
    }
	
	public function actionDoprint($rid)
	{
		$id = Yii::$app->user->id;
		$model = new Application2;
		$model->applid = $rid;
		
		if (isset($_POST['btnCert'])) 
		{
		  
		  $qi = "SELECT r.cdate FROM invoice i INNER JOIN receipts r ON i.invoicenum = r.invoicenum WHERE i.applid = '$rid'";
		  $sdate = Yii::$app->db->createCommand($qi)->queryScalar(); 
		 $q = "UPDATE application SET printed ='Y',prdate =NOW(),prby ='$id',csdate ='$sdate',cedate =DATE_ADD('$sdate',INTERVAL 1 YEAR) ";
		 $q .="WHERE applid = '$rid'";
		 Yii::$app->db->createCommand($q)->execute();
		
		$this->redirect(['management/print','pid'=>$rid]);
		}
		
		return $this->render('_fdop',['model'=>$model,'tbInfo'=>$this->getInfo($rid),'tbInfo2'=>$this->getCInfo($rid)]);
	}
	
	public function actionPrint($pid)
	{
		$data = '';
		
		$q = "SELECT a.applid,b.bname,a.paddress,b.uses,DATE_FORMAT(a.csdate,'%d/%m/%Y'),DATE_FORMAT(a.cedate,'%d/%m/%Y'),";
		$q .="CONCAT(u.fname,' ',IFNULL(u.mname,''),' ',u.sname),t.sname FROM application a INNER JOIN poccupied b ON a.applid = b.applid ";
		$q .="INNER JOIN users u ON u.userid = a.prby INNER JOIN jobtitles t ON t.titleid = u.titleid WHERE a.applid = '$pid' LIMIT 1";
		
		$data = Yii::$app->db->createCommand($q)->queryOne(0);
		$this->layout = 'print/main.php';
		echo $this->render('print',['data'=>$data]);
		
	}
	
	public function actionClients()
    {
        if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		  $model = new Clients;
		if(isset($_POST['btnSName']))
			{
				$model->load(Yii::$app->request->post());
				$sname = trim(str_replace("'","''",$model->name));
				$q2 ="SELECT @s:=@s+1 as Sn,name as 'Client Name',CONCAT('+',pcode,phone) as 'Phone Number',email as Email,";
		        $q2 .="ctype as 'Client Type',DATE_FORMAT(cdate,'%d/%m/%Y') as 'Registered Date',cid FROM ";
		        $q2 .="clients,(SELECT @s:=0) AS s WHERE LOWER(name) LIKE LOWER('%$sname%')";
		        
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM clients WHERE LOWER(name) LIKE LOWER('%$sname%')")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Client Name','Client Type','Registered Date'],],
							'pagination'=>[
							               'pageSize'=>10,
	                                      ],
						    ]);	
      
         	  
		return $this->render('clients',['dataProvider'=>$dataProvider,'model'=>$model]);
		}
		else
		{
		$q2 ="SELECT @s:=@s+1 as Sn,name as 'Client Name',CONCAT('+',pcode,phone) as 'Phone Number',email as Email,";
		$q2 .="ctype as 'Client Type',DATE_FORMAT(cdate,'%d/%m/%Y') as 'Registered Date',cid FROM ";
		$q2 .="clients,(SELECT @s:=0) AS s ORDER BY name";
		
		
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM clients")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Client Name','Client Type','Registered Date'],],
							'pagination'=>[
							               'pageSize'=>10,
	                                      ],
						    ]);	
      
         	  
		return $this->render('clients',['dataProvider'=>$dataProvider,'model'=>$model]);
		}
    }
	
	
	public function actionNc()
	{
		 unset($_SESSION['cid']);
		return $this->redirect(['operation/newclient']);
	}
	
	public function actionEditcl($cid)
	{
		$_SESSION['cid'] = $cid;
		return $this->redirect(['operation/newclient']);
	}
	
	
	public function actionNewclient()
	{
		  
		 if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		 
         $m1 = $m2 = false;
		 $none = true;
		 $cid = '';
		 $model = new Clients;
		 
		 if(isset($_SESSION['cid'])) 
		    {
			 $model = $this->loadClient($_SESSION['cid']);
			      Yii::$app->session->setFlash('ssuccess',"Client Details.");
			     $cid = $_SESSION['cid'];
				 $none = false;
				 $m2 = true;
		    }
			
		if(isset($_POST['btnRegister'])) 
		 {
		    $m1 = true;   
		   if($model->load(Yii::$app->request->post()) && $model->save())
		   {
			 $none = false;
			 $cid = $model->cid;
			 
			 Yii::$app->session->setFlash('ssuccess',"Client has been  Successful Registered.");
			 $m1 = false;
			$m2 = true;
		   }
			   
		 }
		 
		if($none)
		{
			$m1 = true;
		}
         return $this->render('tabs',['model'=>$model,'cid'=>$cid,'m1'=>$m1,'m2'=>$m2]);
									 
	}
	
	public function actionViews($cid)
	{
		
		return $this->render('vws',['tbData'=>$this->getClientDetails($cid)]);
	}
	
	
	public function getClientDetails($cid)
	{
		
	    $qc = "SELECT c.name,CONCAT('+',c.pcode,c.phone),c.pcode2,c.phone2,c.address,c.paddress,";
		$qc .="CONCAT(e.fname,' ',e.mname,' ',e.sname),c.cdate FROM clients c INNER JOIN employees e ON e.empid = c.cby ";
		$qc .="WHERE c.cid =:cid";
		
		$dt = Yii::$app->db->createCommand($qc)->bindParam(':cid',$cid)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table class='table table-bordered table-gray'><thead><tr><th colspan=2>CLIENTS DETAILS</th></tr></thead>";
			 $tbH .="<tr><th width=20%>Client Name</th><td>$dt[0]</td></tr>";
			 $tbH .="<tr><th>Telephone Number</th><td>$dt[1]</td></tr>";
			 if(!empty($dt[3]))
			 {
			 $tbH .="<tr><th>Telephone Number2</th><td>+$dt[2]$dt[3]</td></tr>";
			 }
			 $tbH .="<tr><th>Client Postal Address</th><td>$dt[4]</td></tr>";
			 $tbH .="<tr><th>Client Physical Address</th><td>$dt[5]</td></tr>";
			 $tbH .="<tr><th>Registered By</th><td>$dt[6]</td></tr>";
			 $tbH .="<tr><th>Registered Date</th><td>$dt[7]</td></tr></table>";
		 }  
		return $tbH.$tbTr;
		
	}
	
	public function getCP()
	{
		$supid = $_SESSION['supid'];
		$q = "SELECT fullname,title,phone,email,id FROM supcontacts WHERE supid = '$supid' ";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-bordered table-gray footable'><thead>";
		    $tbTr .="<tr><th>SN</th><th>CONTACT PERSON NAME</th><th>TITLE</th><th>PHONE</th><th>EMAIL</th><th>ACTION</th></tr></thead>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['operation/editcp','id'=>$rs[4]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	protected function loadClient($cid)
    {
        if (($model = Clients::findOne($cid)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	protected function loadCP($id)
    {
        if (($model = Supcontacts::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	protected function loadSupplier($id)
    {
        if (($model = Suppliers::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
    public function actionAssign($rid) 
	{
        if(!FireRoles::isAssigner())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		
		$model = new Assignments;
		$model->applid = $rid;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->db->createCommand("UPDATE application SET status = 'STAGE3',curid ='$model->userid' WHERE applid ='$model->applid'")->execute();
		  $fullname = Yii::$app->db->createCommand("SELECT CONCAT(fname,' ',IFNULL(mname,''),' ',sname) FROM users WHERE userid = '$model->userid'")->queryScalar();
		  Yii::$app->session->setFlash('asuccess',"Request Successful Asigned to <b>$fullname</b>.");
		  return $this->redirect(['management/index']);
        }
		
        return $this->render('_fassign',['model'=>$model,'tbInfo'=>$this->getInfo($rid)]);
    }
	
	public function actionSuccess()
	{
		return $this->render('success');
	}
	
	
	
	public function actionRostep1() 
	{
        if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		unset($_SESSION['orderno']);
		unset($_SESSION['bno']);
		unset($_SESSION['JobItems']);
		$model = new RO;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		 // $exp = explode(":",$model->itemid);
		 // $_SESSION['iid'] = $exp[0];
		 // $_SESSION['weight'] = $exp[1];
		  $_SESSION['orderno'] = $model->orderno;
		  $_SESSION['bno'] = $model->batchno;
		  return $this->redirect(['operation/rorders']);
        }
		
        return $this->render('_fro',['model'=>$model]);
    }
	
	public function actionRorders() 
	{
        if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		
		$model = new Rorders;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		  $id = Yii::$app->user->id;
		  $orderno = $_SESSION['orderno'];
		  $bno = $_SESSION['bno'];
		  $pid = explode(":",$model->prodid);
		  $cnt = $model->quantity;
		 for($i = 1; $i <= $cnt; $i++)
		 {			 
		   $qo = "SELECT id FROM orderitems WHERE orderno ='$orderno' AND prodid ='$pid[0]' AND status ='O' LIMIT 1";
		   $gid = Yii::$app->db->createCommand($qo)->queryScalar();
			
			$max = Yii::$app->db->createCommand("SELECT COUNT(id) FROM barcodes WHERE prodid = '$pid[0]'")->queryScalar();
		    $bcode = $pid[1].($max + 1);
			
			 $qi = "INSERT INTO barcodes(prodid,barcode,used,refid,printed) VALUES('$pid[0]','$bcode','Y','$orderno','N')"; 
		     Yii::$app->db->createCommand($qi)->execute();
			
		  
	      $qup ="UPDATE orderitems SET prodid ='$pid[0]',barcode ='$bcode',dweight ='$model->dweight',dwunit = '$model->dwunit',";
		  $qup .="status ='R',batchno = '$bno',rdate = CURDATE(), rtime = CURTIME(), locid ='$model->locid',rby = '$id',";
		  $qup .="rweight ='$model->dweight',rwunit ='$model->dwunit' WHERE id ='$gid'";
		  Yii::$app->db->createCommand($qup)->execute();
		 
	       $a = Yii::$app->db->createCommand("SELECT COUNT(id) FROM orderitems WHERE orderno ='$orderno'")->queryScalar();
	       $b = Yii::$app->db->createCommand("SELECT COUNT(id) FROM orderitems WHERE orderno ='$orderno' AND status ='R'")->queryScalar();
	
	      if($a == $b)
		  {
			Yii::$app->db->createCommand("UPDATE orders SET ostatus = 'R' WHERE orderno ='$orderno'")->execute();  
		  }
		}
		  $model->prodid ='';
		  $model->quantity ='';
		  
		  $model->locid ='';
		  $model->dweight ='';
		   Yii::$app->session->setFlash('rsuccess',"Goods Received and Successful Saved.");
        }
		
        return $this->render('_frorders',['model'=>$model,'tbData'=>$this->getIDetails()]);
    }
	
	
	public function getIDetails()
	{
		
		$bno = $_SESSION['bno'];
		$orderno = $_SESSION['orderno'];
		$qs = "SELECT s.name,o.orderno,DATE_FORMAT(o.orderdate,'%d/%m/%Y'),DATE_FORMAT(o.expddate,'%d/%m/%Y') FROM orders o ";
		$qs .="INNER JOIN suppliers s ON s.supid = o.supid WHERE o.orderno = '$orderno'";
		
		$dt = Yii::$app->db->createCommand($qs)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table class='table table-bordered table-gray'>";
			 $tbH .="<thead><tr><th>SUPPLIER</th><th>ORDER NO</th><th>ORDER DATE</th><th>EXPECTED DELIVERY DATE</th></tr></thead>";
			 $tbH .="<tr><td><b>$dt[0]</b></td><td><b>$dt[1]</b></td><td><b>$dt[2]</b></td><td><b>$dt[3]</b></td></tr></table>";
			 
			 $qd = "SELECT p.name,o.batchno,o.dweight,o.dwunit,DATE_FORMAT(o.rdate,'%d/%m/%Y'),COUNT(o.id) FROM orderitems o ";
			 $qd .="INNER JOIN products p ON p.prodid = o.prodid WHERE o.orderno ='$orderno' AND o.status != 'O' GROUP BY ";
			 $qd .="p.name,o.batchno,o.dweight,o.dwunit,o.rdate";
			 
			 $rst = Yii::$app->db->createCommand($qd)->queryAll(false);
			  if(!empty($rst))
			  {
				  $i = 1;
			$tbD ="<table class='table table-bordered'><tr><td><b>Sn</b></td><td><b>Product Received</b></td><td><b>Batch No</b></td>";
				  $tbD .="<td><b>Weight</b></td><td><b>Received Date</b></td><td><b>Received Units</b></td></tr>";
				  $tt = 0;
				  foreach($rst as $rs)
				  {
					$tt = $tt + $rs[5];
					$tbD .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]$rs[3]</td><td>$rs[4]</td><td>$rs[5]</td></tr>"; 
                     $i++;					
				  }
				 $tbD .="<tr><td colspan=5 align=right><b><i>Total:</i></b></td><td><b>".number_format($tt)."</b></td></tr></table>"; 
			  }
			  
			
			
		  }
		return $tbH.$tbD;
		
	}
	
	public function actionGetquantities()
	{
		$data = [];
		$orderno = $_SESSION['orderno'];
         if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $nat = $parents[0];
			   $n = explode(":",$nat);
			   $q ="SELECT @s:=@s+1 AS id,@s:=@s AS name FROM orderitems,(SELECT @s:=0) AS id WHERE prodid ='$n[0]' AND ";
			   $q .="orderno ='$orderno' AND STATUS = 'O'";
			   
           	   $data = Yii::$app->db->createCommand($q)->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
	}
	
	public function actionGetitems()
	{
		$data = [];
         if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $nat = $parents[0];
			   $q = "SELECT DISTINCT CONCAT(o.prodid,':',o.weight) id,CONCAT(p.name,' : ',o.weight,' ',o.munit) name FROM orderitems o INNER JOIN products p ON p.prodid = o.prodid WHERE ";
			   $q .="o.orderno = '$nat' AND o.status = 'O' GROUP BY o.itemid,o.weight";
           	   $data = Yii::$app->db->createCommand($q)->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
	}
	
	
	public function actionEuser($id) 
	{
         if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		$model = $this->loadUser($id);
		$model->rid = Yii::$app->db->createCommand("SELECT rid FROM userrole WHERE userid = '$id'")->queryScalar();
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  $q = "UPDATE userrole SET rid ='$model->rid' WHERE userid = '$model->userid'";
		  Yii::$app->db->createCommand($q)->execute();

		  Yii::$app->session->setFlash('usuccess',"System User Details Updated Successful.");
		  return $this->redirect(['management/adduser']);
        }
		
        return $this->render('_fuser2',['model'=>$model,'tbUser'=>$this->getUsers()]);
    }
	
	
	protected function loadUser($id)
    {
        if (($model = Users2::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function getUsers()
	{
	$q = "SELECT u.ucode,CONCAT(u.fname,' ',IFNULL(u.mname,''),' ',u.sname),t.name,z.name,r.name,u.status,u.userid FROM users u INNER JOIN jobtitles t ON ";
		$q .="t.titleid = u.titleid INNER JOIN zones z ON z.zid = u.zid INNER JOIN userrole ur ON u.userid = ur.userid INNER JOIN roles r ON ";
		$q .=" r.rid = ur.rid ORDER BY fname";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>REG #</th><th>FULLNAME</th><th>TITLE</th><th>ZONE</th><th>ROLE</th><th>USER STATUS</th><th>ACTION</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]</td><td>$rs[5]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['management/euser','id'=>$rs[6]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	public function actionJtitles() 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		  
		$model = new Jobtitles;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('jsuccess',"Jobtitle Successful added to the System.");
		  return $this->refresh();
        }
		
        return $this->render('_fjt',['model'=>$model,'tbJt'=>$this->getJt()]);
    }
	
	public function actionEjt($id) 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		  
		$model = $this->loadJt($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('jsuccess',"Jobtitle Details Updated Successful.");
		  return $this->redirect(['management/jtitles']);
        }
		
        return $this->render('_fjt',['model'=>$model,'tbJt'=>$this->getJt()]);
    }
	
	
	protected function loadJt($id)
    {
        if (($model = Jobtitles::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function getJt()
	{
	    $q = "SELECT name,sname,titleid FROM jobtitles ORDER BY name ";
		
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>NAME</th><th>SHOT NAME(INITIALS)</th><th>ACTION</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['management/ejt','id'=>$rs[2]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	
	public function actionAzone() 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		
		$model = new Zones;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('jsuccess',"Zone Successful added to the System.");
		  return $this->refresh();
        }
		
        return $this->render('_fazone',['model'=>$model,'tbZone'=>$this->getZn()]);
    }
	
	public function actionEzone($id) 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		$model = $this->loadZn($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('jsuccess',"Zone name Updated Successful.");
		  return $this->redirect(['management/azone']);
        }
		
        return $this->render('_fazone',['model'=>$model,'tbZone'=>$this->getZn()]);
    }
	
	
	protected function loadZn($id)
    {
        if (($model = Zones::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function getZn()
	{
	    $q = "SELECT name,zid FROM zones ORDER BY name ";
		
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>ZONE NAME</th><th>ACTION</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['management/ezone','id'=>$rs[1]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	
	//////////////////////////////////////////////////////////////////////////////////
	//REGISTER REGIONS
	
	public function actionAregion() 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		$model = new Regions;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('rsuccess',"Region Successful added to the System.");
		  return $this->refresh();
        }
		
        return $this->render('_faregion',['model'=>$model,'tbZone'=>$this->getRn()]);
    }
	
	public function actionEregion($id) 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		$model = $this->loadRegion($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('rsuccess',"Region name Updated Successful.");
		  return $this->redirect(['management/aregion']);
        }
		
        return $this->render('_faregion',['model'=>$model,'tbZone'=>$this->getRn()]);
    }
	
	
	protected function loadRegion($id)
    {
        if (($model = Regions::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function getRn()
	{
	    $q = "SELECT name,rid FROM regions ORDER BY name ";
		
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>REGION NAME</th><th>ACTION</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['management/eregion','id'=>$rs[1]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////
	///REGISTER DISTRICT
	
	public function actionAdistrict() 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		$model = new Districts;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('dsuccess',"District Successful added to the System.");
		  return $this->refresh();
        }
		
        return $this->render('_fadistrict',['model'=>$model,'tbZone'=>$this->getDn()]);
    }
	
	public function actionEdistrict($id) 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		$model = $this->loadDistrict($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('dsuccess',"District Detail Updated Successful.");
		  return $this->redirect(['management/adistrict']);
        }
		
        return $this->render('_fadistrict',['model'=>$model,'tbZone'=>$this->getDn()]);
    }
	
	
	protected function loadDistrict($id)
    {
        if (($model = Districts::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function getDn()
	{
	    $q = "SELECT r.name,d.name,d.did FROM districts d INNER JOIN regions r ON r.rid = d.rid ORDER BY r.name,d.name ";
		
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>REGION NAME</th><th>DISTRICT NAME</th><th>ACTION</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['management/edistrict','id'=>$rs[2]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	/////////////////////////////////////////
	
	
	public function getOwners()
	{
		$apid = $_SESSION['applid'];
		$rst = Yii::$app->db->createCommand("SELECT fullname,address,telno,email,id FROM powners WHERE applid = '$apid'")->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>Owner Name</th><th>Address</th><th>Telephone</th><th>Email</th><th>Edit</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['site/editowner','id'=>$rs[4]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	public function getOPremises()
	{
		$apid = $_SESSION['applid'];
		$rst = Yii::$app->db->createCommand("SELECT bname,uses,flno,id FROM poccupied WHERE applid = '$apid'")->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>Ocupier/Trading Name</th><th>Usage</th><th>Floor</th><th>View</th><th>Edit</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td>";
				$tbTr .="<td>".Html::a("<b>View More</b>",['site/viewop','id'=>$rs[3]])."</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['site/editowner','id'=>$rs[3]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	
	
	
	public function getOuses()
	{
		$apid = $_SESSION['applid'];
		$q = "SELECT descr,id FROM pouses WHERE applid = '$apid'";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>".Html::a("<b>Edit</b>",['site/editffequip','id'=>$rs[4]])."</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$rs[0]</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	public function getStatus()
	{
		if(isset($_SESSION['applid']))
		{
			return true;
		}
		return false;
	}
	
	public function getSummary()
	{
		$all = true;
		$tbTr ="<table class='table table-striped table-bordered'>";
		if(isset($_SESSION['applid']))
		{
			$apid = $_SESSION['applid'];
			
			
			$st1 = Yii::$app->db->createCommand("SELECT count(*) FROM application WHERE applid ='$apid'")->queryScalar();
			 
			  if($st1 > 0)
			  {
				  $tbTr .="<tr><td>1.Applicant And Premise Information - <b>Done.</b></td></tr>";
			  }
			  else
			  {
				  $all = false;
				  $tbTr .="<tr><td><font color=red>1.Applicant And Premise Information - <b>Incomplete.</b></font></td></tr>";
			  }
			  
			  $st2 = Yii::$app->db->createCommand("SELECT count(*) FROM powners WHERE applid ='$apid'")->queryScalar();
			 
			  if($st2 > 0)
			  {
				  $tbTr .="<tr><td>2.Premise(s) Ownership Information - <b>Done.</b></td></tr>";
			  }
			  else
			  {
				  $all = false;
				  $tbTr .="<tr><td><font color=red>2.Premise(s) Ownership Information - <b>Incomplete.</b></font></td></tr>";
			  }
			  
			  $st3 = Yii::$app->db->createCommand("SELECT count(*) FROM poccupied WHERE applid ='$apid'")->queryScalar();
			 
			  if($st3 > 0)
			  {
				  $tbTr .="<tr><td>3.Occupied Premise(s) Information - <b>Done.</b></td></tr>";
			  }
			  else
			  {
				  $all = false;
				  $tbTr .="<tr><td><font color=red>3.Occupied Premise(s) Information - <b>Incomplete.</b></font></td></tr>";
			  }
			  
			  $st4 = Yii::$app->db->createCommand("SELECT count(*) FROM pexplosive WHERE applid ='$apid'")->queryScalar();
			 
			  if($st4 > 0)
			  {
				  $tbTr .="<tr><td>4.Nature And Quality of Exposive in the Premise Information - <b>Done.</b></td></tr>";
			  }
			  else
			  {
				  $all = false;
				  $tbTr .="<tr><td><font color=red>4.Nature And Quality of Exposive in the Premise Information - <b>Incomplete.</b></font></td></tr>";
			  }
			  
			  $st5 = Yii::$app->db->createCommand("SELECT count(*) FROM pffequip WHERE applid ='$apid'")->queryScalar();
			 
			  if($st5 > 0)
			  {
				  $tbTr .="<tr><td>5.Detail of Fire Fighting Equipment Available - <b>Done.</b></td></tr>";
			  }
			  else
			  {
				  $all = false;
				  $tbTr .="<tr><td><font color=red>5.Detail of Fire Fighting Equipment Available - <b>Incomplete.</b></font></td></tr>";
			  }
			  
			  $st6 = Yii::$app->db->createCommand("SELECT count(*) FROM pouses WHERE applid ='$apid'")->queryScalar();
			 
			  if($st6 > 0)
			  {
				  $tbTr .="<tr><td>6.Usage of Other Part of Building - <b>Done.</b></td></tr>";
			  }
			  else
			  {
				  $all = false;
				  $tbTr .="<tr><td><font color=red>6.Usage of Other Part of Building - <b>Incomplete.</b></font></td></tr>";
			  }
			  

              if($all)
			  {
				  $tbTr .="<td>".Html::a("<b>Submit Application</b>",['site/submit'])."</td></tr></table>";
			  }
			  else
			  {
				  $tbTr .="<td><div class='alert alert-warning'><b>Please Fill All Sections Mark Red before trying to submit again.</div></td></tr></table>";
			  }	  
				  	
		}
		else
		{
		  $tbTr .="<td><div class='alert alert-warning'><b>Please Fill All Sections before trying to submit again.</div></td></tr></table>";	
		}
		return $tbTr;
	}
	
	protected function loadApl($id)
    {
        if (($model = Application::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function actionNoaccess()
	{
		return $this->render('noaccess');
	}


}
