<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\data\SqlDataProvider;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\LoginForm2;
use app\models\ContactForm;
use app\models\Trace;
use app\models\Users;
use app\models\Users2;
use app\models\Jobtitles;
use app\models\Rates;
use app\models\Regions;
use app\models\FrostanRoles;
use yii\helpers\Html;
use yii\helpers\Json;

class AdminController extends Controller
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
	 
	 public function actionCpwd() 
	{
        $model = new LoginForm2;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		  $id = Yii::$app->user->id;
		   $pwd = Yii::$app->security->generatePasswordHash($model->password);
		   $q ="UPDATE users SET pwd = '$pwd' WHERE userid ='$id'";
		   Yii::$app->db->createCommand($q)->execute();
		   
		  Yii::$app->session->setFlash('psuccess',"Your Password has been Successful Changed.");
		  return $this->redirect(['management/showp']);
        }
		
        return $this->render('_fcpwd',['model'=>$model]);
    }
	
	public function actionShowp()
	{
		return $this->render('vwps');
	}
     public function actionIndex()
    {
        
		if(!FrostanRoles::isAdmin())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		return $this->render('index',['model'=>$model,'tbTrace'=>$tbTrace]);
    }
	
	public function actionApprove()
    {
        
		if(!FireRoles::isApprover())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		
        $q2 ="SELECT CONCAT(a.fname,' ',IFNULL(a.mname,''),' ',a.sname) as Client,a.telno as Telephone,a.pshno as House,";
		$q2 .="a.paddress as Address,r.finspectors as 'Fire Inspectors',r.cdate as 'Date',a.applid,r.id FROM application a INNER JOIN reports r ON ";
		$q2 .="a.applid = r.applid WHERE a.status = 'STAGE4' ORDER BY r.cdate";
		
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM application WHERE status= 'STAGE3' AND curid ='$id'")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Client','Registered Date'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);	
      
         	  
		return $this->render('approve',['dataProvider'=>$dataProvider]);
    }
	
	public function actionDoapprove($rid)
	{
		$_SESSION['reqid'] = $rid;
		unset($_SESSION['JobItems']);
		return $this->redirect(['doapr']);
	}
	
	
	public function actionDoapr()
	{
		
		if(!FireRoles::isApprover())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		  $rid = $_SESSION['reqid'];
		  $model = $this->loadApr($rid);
		  $hasTotal = false;
		  
		  if(isset($_POST['btnGeneral'])) 
		  {
			 $model->load(Yii::$app->request->post());
			 $model->save(false);
             
			 $id = Yii::$app->user->id;
			 $apid = $model->applid;
			 $invno = Yii::$app->db->createCommand("SELECT CONCAT(invcode,invnumber) FROM accode")->queryScalar();
		     Yii::$app->db->createCommand("UPDATE accode SET invnumber=invnumber + 1")->execute();
		     $Total = 0;
		  if($_SESSION['JobItems'])
		   {	
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				      $ck = explode(":",$ckey);
				      $Total = $Total + $ck[1];
					  
		                $qhc ="INSERT INTO invoiceitem(invoicenum,applid,serviceid,amount,status,regby,regdate)";
                        $qhc .=" VALUES('$invno','$apid','$ck[0]','$ck[1]','N','$id',NOW())";
		                Yii::$app->db->createCommand($qhc)->execute();
				   
		        }

		       
					 $qinv ="INSERT INTO invoice(invoicenum,applid,pricebeforetax,vat,tamount,wpercent,wamount,"; 
                     $qinv .="amountbw,status,invoicedate,regby,regdate,vatexempted,crtime) VALUES('$invno','$apid','$Total',";
                     $qinv .="0,'$Total',0,0,'$Total','N',CURDATE(),'$id',CURDATE(),'$N',CURTIME())";
					
		              Yii::$app->db->createCommand($qinv)->execute();
					  
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						
			 Yii::$app->db->createCommand("UPDATE application SET status = 'STAGE5' WHERE applid ='$apid'")->execute();
			 Yii::$app->session->setFlash('apsuccess','Fire Inspection report successful Approved.Request has been sent to Finance for Payment Process');
			 $this->redirect(['approve']);
		               
	          }
	
			 
							 
		  }
		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				
				$_SESSION['JobItems'][$model->items.':'.$model->price]++;  //add this item to the session variable 
			    $this->refresh(); 
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
		   $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Charged Item</th>";
		   $tbInvoice .="<th>Charges</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $rs = Yii::$app->db->createCommand("SELECT name FROM charges WHERE cid ='$ck[0]'")->queryScalar();
					  $price = $ck[1];
					  $Total = $Total + $price;
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['management/rmitem','cid'=>$ckey])."</b></td>";
					 $tbInvoice .="<td>$rs</td><td>".number_format($price,2)."</td></tr>";
					$i++; 
				  }
			 $tbInvoice .="<tr><td colspan=3 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 $tbInvoice .="</table>";
		return $this->render('_fapr',['model'=>$model,'tbDet'=>$this->getRDet($rid),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
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
		     $q ="SELECT cid,name FROM charges WHERE NOT cid IN('$RS')";
		  }
		  else
		  {
			$q ="SELECT cid,name FROM charges";
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
	    return $this->redirect(['doapr']);
	}
	protected function loadApr($id)
    {
        if (($model = Report5::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	public function getRDet($rid)
	{
		$q = "SELECT a.address,rg.name,d.name,r.docid,a.pshno,r.prtno,r.strno,z.name,r.rspart,r.rsaddress,r.rstelno,r.finspectors,";
	    $q .="r.finsdate,r.fromt,r.tot,r.ecname1,r.ectelno1,r.ecname2,r.ectelno2,r.gpuse,r.spuse,r.constrtype,r.pcconstr,r.constrmethod,";
		$q .="r.constryear,r.strtype,r.strheight,r.storeyno,r.gfa,r.tfa,r.pmanagement,r.svalue,r.noexit,r.ifinish,r.pstairways,";
		$q .="r.pvshafts,r.pfopening,r.pwopenings,r.esquality,r.elsigns,r.rcovering,r.paccess,r.adetection,r.aacapacity,r.tssystem,";
		$q .="r.cssystem,r.stsystem,r.rfflow,r.wstype,r.awsupply,r.orfcontrol,r.epd,r.lbno,r.remarks FROM reports r INNER JOIN application a ON ";
		$q .="a.applid = r.applid INNER JOIN zones z ON z.zid = r.zid INNER JOIN districts d ON d.did = a.did INNER JOIN ";
		$q .="regions rg ON rg.rid = d.rid WHERE r.id =:rid";
		
		$rs = Yii::$app->db->createCommand($q)->bindValue(':rid',$rid)->queryOne(0);
		
		$tbData = "<table class='table table-bordered'><tr><td colspan=8 bgcolor='grey'><b>FIRE RESCUE INSPECTION REPORT</b></td></tr>";
		
		if($rs)
		{
			$tbData .="<tr><td>Address</td><td><b>$rs[0]</b></td><td>Region</td><td><b>$rs[1]</b></td>";
			$tbData .="<td>District</td><td><b>$rs[2]</b></td><td>Document No</td><td><b>$rs[3]</b></td></tr>";
			
			$tbData .="<tr><td>Property Name</td><td><b>$rs[4]</b></td><td>Property No</td><td><b>$rs[5]</b></td>";
			$tbData .="<td>Strucure No</td><td><b>$rs[6]</b></td><td>Fire Station Zone</td><td><b>$rs[7]</b></td></tr>";
			
			$tbData .="<tr><td>Responsible Party</td><td><b>$rs[8]</b></td><td>Address</td><td><b>$rs[9]</b></td>";
			$tbData .="<td colspan=2>Telephone</td><td><b>$rs[10]</b></td></tr>";
			
			$tbData .="<tr><td>Fire Inspectors</td><td><b>$rs[11]</b></td><td>Date</td><td><b>$rs[12]</b></td>";
			$tbData .="<td>Arrived Time</td><td><b>$rs[13]</b></td><td>Departed Time</td><td><b>$rs[14]</b></td></tr>";
			
			$tbData .="<tr><td>Emergency Contact</td><td><b>$rs[15]</b></td><td>Phone Number</td><td><b>$rs[16]</b></td>";
			$tbData .="<td>Emergency Contact2</td><td><b>$rs[17]</b></td><td>Phone Number</td><td><b>$rs[18]</b></td></tr>";
			
			$tbData .="<tr><td>General Property Use</td><td colspan=3>$rs[19]</td>";
			$tbData .="<td>Number of Specific Property Uses</td><td colspan=3><b>$rs[20]</b></td></tr>";
			
			$tbData .="<tr><td>Type Of Construction</td><td><b>$rs[21]</b></td><td colspan=2>% Of Combustible Contruction</td><td><b>$rs[22]</b></td>";
			$tbData .="<td>Method of Construction</td><td><b>$rs[23]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Year of Construction</td><td colspan=2><b>$rs[24]</b></td>";
			$tbData .="<td colspan=2>Structure Type</td><td colspan=2><b>$rs[25]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Structure Height</td><td colspan=2><b>$rs[26]</b></td>";
			$tbData .="<td colspan=2>Number of Storey</td><td colspan=2><b>$rs[27]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Ground Floor Area</td><td colspan=2><b>$rs[28]</b></td>";
			$tbData .="<td colspan=2>Total Floor Area</td><td colspan=2><b>$rs[29]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Property Management</td><td colspan=2><b>$rs[30]</b></td>";
			$tbData .="<td colspan=2>Sound Value</td><td colspan=2><b>$rs[31]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Number of Exits/Exit Discharge Width</td><td colspan=2><b>$rs[32]</b></td>";
			$tbData .="<td colspan=2>Interior Finish in Egress routes</td><td colspan=2><b>$rs[33]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Protection of Stairways</td><td colspan=2><b>$rs[34]</b></td>";
			$tbData .="<td colspan=2>Protection of Vertical Shafts</td><td colspan=2><b>$rs[35]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Protection of Floor Opening</td><td colspan=2><b>$rs[36]</b></td>";
			$tbData .="<td colspan=2>Protection of wall openings</td><td colspan=2><b>$rs[37]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Electrical service quality</td><td colspan=2><b>$rs[38]</b></td>";
			$tbData .="<td colspan=2>Emergency lighting and exit signs</td><td colspan=2><b>$rs[39]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Roof Covering</td><td colspan=2><b>$rs[40]</b></td>";
			$tbData .="<td colspan=2>Perimeter Access</td><td colspan=2><b>$rs[41]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Automatic detection</td><td colspan=2><b>$rs[42]</b></td>";
			$tbData .="<td colspan=2>Automatic Alarm Capacity</td><td colspan=2><b>$rs[43]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Type of Sprinkler System</td><td colspan=2><b>$rs[44]</b></td>";
			$tbData .="<td colspan=2>Coverage of Sprinkler System</td><td colspan=2><b>$rs[45]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Standpipe System</td><td colspan=2><b>$rs[46]</b></td>";
			$tbData .="<td colspan=2>Required Fire Flow</td><td colspan=2><b>$rs[47]</b></td></tr>";
			
			$tbData .="<tr><td colspan=2>Water Supply Type</td><td colspan=2><b>$rs[48]</b></td>";
			$tbData .="<td colspan=2>Available Water Supply</td><td colspan=2><b>$rs[49]</b></td></tr>";
			
			$tbData .="<tr><td colspan=8>Obstacles to Rescue and Fire Control:<b>$rs[50]</b></td></tr>";
			$tbData .="<tr><td colspan=2>Evacuation Plan and Drills</td><td colspan=2><b>$rs[51]</b></td>";
			
			$tbData .="<td colspan=2>Log Book</td><td colspan=2><b>$rs[52]</b></td></tr>";
			$tbData .="<tr><td colspan=8><b>Remarks:</b>$rs[53]</td></tr></table>";
			
			return $tbData;
			
		}
		else
		{
			return $tbData .= "<tr><td><b>Invalid Request</td></tr></table>";
		}
		
		
	}
	
	public function actionDoinv()
	{
		 $apid = $_SESSION['reqid'];       
		if(!FireRoles::isApprover())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		  
		  
	}
	
	public function actionInvoice()
    {
        
		if(!FireRoles::isAccount())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		
$q2 ="SELECT CONCAT(a.fname,' ',IFNULL(a.mname,''),' ',a.sname) as Client,telno as Telephone,pshno as House,i.invoicenum as Invoice,";
		$q2 .="CONCAT(DATE_FORMAT(i.regdate,'%d/%m/%Y'),' ',i.crtime) as 'Date',FORMAT(i.tamount,2) as Amount,i.invoicenum FROM ";
		$q2 .="application a INNER JOIN invoice i ON a.applid = i.applid  WHERE a.status = 'STAGE5' AND ";
		$q2 .=" NOT i.invoicenum IN (SELECT invoicenum FROM receipts) ORDER BY i.regdate";
		
		$q3 = "SELECT COUNT(invoicenum) FROM invoice WHERE NOT invoicenum IN(SELECT invoicenum FROM receipts)";
		$cnt = Yii::$app->db->createCommand($q3)->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Client','Date'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);	
      
         	  
		return $this->render('inv',['dataProvider'=>$dataProvider]);
    }
	
	public function actionPrintinv($rid)
	{
		$this->layout = 'print/main.php';
		
	  $Total = 0;
      $VAT = 0;
      $BigTotal = 0;
	  $conn = Yii::$app->db;
      putenv("TZ=Africa/Nairobi");
	    
      
        $qci ="SELECT 'Jeshi la Zimamoto na Uokoaji','9 Barabara ya Ohio','5821','+255-22-2181093','+255-22-2181093',' ','Dar es salaam','NA','NA','fire.rescue@frf.go.tz'";
		$cd = $conn->createCommand($qci)->queryOne(0);
		
		
		 $qpr ="SELECT CONCAT(a.salutation,' ',a.fname,' ',IFNULL(a.mname,''),' ',a.sname),a.pshno,i.wamount,i.wpercent,tamount,i.erate,";
		 $qpr .="i.vat,i.amountbw FROM application a INNER JOIN invoice i ON a.applid = i.applid ";
         $qpr .="WHERE i.invoicenum ='$rid'";
		 
		 $rs = $conn->createCommand($qpr)->queryOne(0);
		 
		$src = Yii::getAlias('@web').'/img/print_logo.png';
        $tbIheader ="<table class='table table-bordered'>";
		$tbIheader .="<tr><td rowspan=5><img src=$src width=113 height=120 /></td>";
        $tbIheader .="<td colspan=2 class=theinv><b><font size=+1>$cd[0]</font></b></td></tr>";
        $tbIheader .="<tr><td class=theinv>$cd[1]</td><td class=theinv>VAT:&nbsp;<b>NA</b></td></tr>";
        $tbIheader .="<tr><td class=theinv>P.O. Box:&nbsp;5821]&nbsp;&nbsp;Dar es salaam</td><td class=theinv>TIN:&nbsp;<b>NA</b></td></tr>";
	    $tbIheader .="<tr><td class=theinv>Telephone:&nbsp;+255-22-2181093&nbsp;&nbsp;Fax:&nbsp;+255-22-2181093</td><td>&nbsp;</td></tr>";
		$tbIheader .="<tr><td class=theinv>Mobile:&nbsp;$cd[5]</td><td class=theinv>Email:&nbsp;fire.rescue@frf.go.tz</td></tr></table>";
			$wamount = $rs[2];
			$wperc = $rs[3];
			$TAmount = $rs[4];
			$ERate = $rs[5];
			$theVAT = $rs[6]; 
			$abw = $rs[7]; 
			
		
$tbCust ="<table class='table table-bordered' >";
 $tbCust .="<tr><td align=right class=theinv>To :</td><td class=theinv><b>$rs[0]</b></td><td align=right class=theinv>Date :</td><td class=theinv><b>".date("d/m/Y")."</b></td></tr>";
 
 $tbCust .="<tr><td align=right class=theinv>House No :</td><td class=theinv><b>$rs[1]</b></td><td align=right class=theinv>Currency:</td><td class=theinv><b>TZS</b></td></tr>";
 
 $tbCust .="<tr><td align=right>Invoice # :</td><td colspan=3><b>$rid</b></td></tr></table>";

  
 
  $tbInvoice ="<table class='table table-bordered' >";
  $tbInvoice .="<tr><th colspan=3 class=theinv>Description of Invoice Items</th></tr>";

			    $tbInvoice .="<tr><th class=theinv>&nbsp;SN</th><th class=theinv>Charges</th><th class=theinv>Amount [TSH]</th></tr>";
				
	           $qit="SELECT c.name,i.amount FROM charges c INNER JOIN invoiceitem i ON ";
	           $qit .="c.cid = i.serviceid WHERE i.invoicenum ='$rid'";
	           $items = $conn->createCommand($qit)->queryAll(false);
			   $i = 1;
			    foreach($items as $item)
			     {
				   $Total = $Total + $item[1];
	               $tbInvoice .="<tr><td class=theinv>&nbsp;$i</td><td class=theinv>$item[0]&nbsp;</td><td class=theinv>".number_format($item[1],2)."</td></tr>";
				   $i++;
			     }
			        
			
		 // $VAT = $BigTotal * 0.18;
		  //$BigTotal = $BigTotal + $VAT;
		   
		  // $tbInvoice .="<tr><td colspan=2 align=right class=theinv><b>VAT - 18%</b></td><td class=theinv><b>".number_format($theVAT,2)."</b></td></tr>";
	      $tbInvoice .="<tr><td colspan=2 align=right class=theinv><b>Total</b></td><td class=theinv><b>". number_format($Total,2)." </b></td></tr>";
		  $tbInvoice .="<tr><td colspan=3 align=center>&nbsp;Prepared by: &nbsp;<i><b>".Yii::$app->user->identity->fullname."</b></i>";
		  $tbInvoice .="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Printed Date:&nbsp;<b>".date("d/m/Y")."</b>&nbsp;&nbsp;&nbsp;Time:&nbsp;<b>".date("h:i:s")."</b></td></tr></table>";
		  

		  
		 /* $tbBank ="<table cellpadding=2 cellspacing=0 width=100% >";
          $tbBank .="<tr><th colspan=9>OUR BANK ACCOUNTS</th></tr>";
		  $tbBank .="<tr><td rowspan=2><b>&nbsp;&nbsp;DTB&nbsp;&nbsp;&nbsp;</b></td><td><i>T.SHS ACCOUNT</i><td>&nbsp;<b>0011755001</b></td>";
		  $tbBank .="<td rowspan=2><b>&nbsp;&nbsp;CRDB&nbsp;&nbsp;&nbsp;</b></td><td><i>T.SHS ACCOUNT</i><td>&nbsp;<b>0150288353200</b></td>";
		  $tbBank .="<td rowspan=2><b>&nbsp;&nbsp;TIB&nbsp;&nbsp;&nbsp;</b></td><td><i>T.SHS ACCOUNT</i><td>&nbsp;<b>004-200-4924-01</b></td></tr>";
		  $tbBank .="<tr><td><i>USD ACCOUNT</i><td>&nbsp;<b>0011755002</b></td>";
		  $tbBank .="<td><i>USD ACCOUNT</i><td>&nbsp;<b>0250288353200</b></td>";
		  $tbBank .="<td><i>USD ACCOUNT</i><td>&nbsp;<b>004-200-4924-02</b></td></tr></table>";*/
		  
		  return $this->render('showinv',['rid'=>$rid,'tbBC'=>$tbBC,'tbHeader'=>$tbIheader,'tbCust'=>$tbCust,'tbInv'=>$tbInvoice,'tbBank'=>$tbBank]);
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
	
	
	public function actionPrintrct($rid)
	{
		$this->layout = 'print/main.php';

	  $Total = 0;
      $VAT = 0;
      $BigTotal = 0;
	  $conn = Yii::$app->db;
      putenv("TZ=Africa/Nairobi");
	    
        $ino = $conn->createCommand("SELECT invoicenum FROM receipts WHERE receiptno = '$rid'")->queryScalar();
		
        $qci ="SELECT 'Jeshi la Zimamoto na Uokoaji','9 Barabara ya Ohio','5821','+255-22-2181093','+255-22-2181093',' ','Dar es salaam','NA','NA','fire.rescue@frf.go.tz'";
		$cd = $conn->createCommand($qci)->queryOne(0);
		
		
		 $qpr ="SELECT CONCAT(a.salutation,' ',a.fname,' ',IFNULL(a.mname,''),' ',a.sname),a.pshno,i.wamount,i.wpercent,tamount,i.erate,";
		 $qpr .="i.vat,i.amountbw FROM application a INNER JOIN invoice i ON a.applid = i.applid ";
         $qpr .="WHERE i.invoicenum ='$ino'";
		 
		 $rs = $conn->createCommand($qpr)->queryOne(0);
		 
		$src = Yii::getAlias('@web').'/img/print_logo.png';
        $tbIheader ="<table class='table table-bordered'>";
		$tbIheader .="<tr><td rowspan=5><img src=$src width=113 height=120 /></td>";
        $tbIheader .="<td colspan=2 class=theinv><b><font size=+1>$cd[0]</font></b></td></tr>";
        $tbIheader .="<tr><td class=theinv>$cd[1]</td><td class=theinv>VAT:&nbsp;<b>NA</b></td></tr>";
        $tbIheader .="<tr><td class=theinv>P.O. Box:&nbsp;5821]&nbsp;&nbsp;Dar es salaam</td><td class=theinv>TIN:&nbsp;<b>NA</b></td></tr>";
	    $tbIheader .="<tr><td class=theinv>Telephone:&nbsp;+255-22-2181093&nbsp;&nbsp;Fax:&nbsp;+255-22-2181093</td><td>&nbsp;</td></tr>";
		$tbIheader .="<tr><td class=theinv>Mobile:&nbsp;$cd[5]</td><td class=theinv>Email:&nbsp;fire.rescue@frf.go.tz</td></tr></table>";
			$wamount = $rs[2];
			$wperc = $rs[3];
			$TAmount = $rs[4];
			$ERate = $rs[5];
			$theVAT = $rs[6]; 
			$abw = $rs[7]; 
			
		
$tbCust ="<table class='table table-bordered' >";
 $tbCust .="<tr><td align=right class=theinv>To :</td><td class=theinv><b>$rs[0]</b></td><td align=right class=theinv>Date :</td><td class=theinv><b>".date("d/m/Y")."</b></td></tr>";
 
 $tbCust .="<tr><td align=right class=theinv>House No :</td><td class=theinv><b>$rs[1]</b></td><td align=right class=theinv>Currency:</td><td class=theinv><b>TZS</b></td></tr>";
 
 $tbCust .="<tr><td align=right>Receipt # :</td><td colspan=3><b>$rid</b></td></tr></table>";

  
 
  $tbInvoice ="<table class='table table-bordered' >";
  $tbInvoice .="<tr><th colspan=3 class=theinv>Description of Receipt Items</th></tr>";

			    $tbInvoice .="<tr><th class=theinv>&nbsp;SN</th><th class=theinv>Charges</th><th class=theinv>Amount [TSH]</th></tr>";
				
	           $qit="SELECT c.name,i.amount FROM charges c INNER JOIN invoiceitem i ON ";
	           $qit .="c.cid = i.serviceid WHERE i.invoicenum ='$ino'";
	           $items = $conn->createCommand($qit)->queryAll(false);
			   $i = 1;
			    foreach($items as $item)
			     {
				   $Total = $Total + $item[1];
	               $tbInvoice .="<tr><td class=theinv>&nbsp;$i</td><td class=theinv>$item[0]&nbsp;</td><td class=theinv>".number_format($item[1],2)."</td></tr>";
				   $i++;
			     }
			        
			
		 // $VAT = $BigTotal * 0.18;
		  //$BigTotal = $BigTotal + $VAT;
		   
		  // $tbInvoice .="<tr><td colspan=2 align=right class=theinv><b>VAT - 18%</b></td><td class=theinv><b>".number_format($theVAT,2)."</b></td></tr>";
	      $tbInvoice .="<tr><td colspan=2 align=right class=theinv><b>Total</b></td><td class=theinv><b>". number_format($Total,2)." </b></td></tr>";
		  $tbInvoice .="<tr><td colspan=3 align=center>&nbsp;Prepared by: &nbsp;<i><b>".Yii::$app->user->identity->fullname."</b></i>";
		  $tbInvoice .="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Printed Date:&nbsp;<b>".date("d/m/Y")."</b>&nbsp;&nbsp;&nbsp;Time:&nbsp;<b>".date("h:i:s")."</b></td></tr></table>";
		  

		  
		  return $this->render('showinv',['rid'=>$rid,'tbBC'=>$tbBC,'tbHeader'=>$tbIheader,'tbCust'=>$tbCust,'tbInv'=>$tbInvoice]);
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
	
	public function actionMytask()
    {
        if(!FireRoles::isInspector())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		
		$id = Yii::$app->user->id;
		
		$q2 ="SELECT CONCAT(a.fname,' ',IFNULL(a.mname,''),' ',a.sname) as Client,a.telno as Telephone,r.name as Region,";
		$q2 .="a.paddress as 'Physical Address',t.comments as Comments,t.cdate as 'Assigned Date',a.applid FROM application a ";
		$q2 .="INNER JOIN assignments t ON a.applid = t.applid INNER JOIN districts d ON d.did = a.did INNER JOIN regions r ON ";
		$q2 .="r.rid = d.rid WHERE a.status = 'STAGE3' AND t.userid ='$id' ORDER BY t.cdate";
		
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM application WHERE status= 'STAGE3' AND curid ='$id'")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Client','Registered Date'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);	
      
         	  
		return $this->render('task',['dataProvider'=>$dataProvider]);
    }
	
	public function actionAtstep1($rid)
	{
		if(!FireRoles::isInspector())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		  $rpid = Yii::$app->db->createCommand("SELECT id FROM reports WHERE applid ='$rid' AND completed ='N'")->bindParam(':rid',$rid)->queryScalar();
		  if($rpid)
		  {
			$_SESSION['rpid'] = $rpid;  
		  }
		  $_SESSION['rid'] = $rid;
		  
		  return $this->redirect(['attend']);
		
	}
	
	
	public function actionAttend()
	{
		  
		 if(!FireRoles::isInspector())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		  $rid = $_SESSION['rid'];
		 
		  
		  
		 
         $m1 = $m2 = $m3 = $m4 = $m5 = $m6 = $m7 = false;
		 $none = true;
		 
		    $model = new Reports();
			$model2 = new Report2;
		    $model3 = new Report3;
		    $model4 = new Report4;
            $model5 = new Poccupied;			
			$model6 = new Pexplosive;
		    $model7 = new Pffequip;
			
			if(isset($_SESSION['rpid'])) 
		    {
			 $model = $this->loadRpt($_SESSION['rpid']);
			 $model2 = $this->loadRpt2($_SESSION['rpid']);
		     $model3 = $this->loadRpt3($_SESSION['rpid']);
		     $model4 = $this->loadRpt4($_SESSION['rpid']);
			 $model5 = $this->loadMod5($_SESSION['rid']);
			 
	$rs =Yii::$app->db->createCommand("SELECT fullname,address,telno,email FROM powners WHERE applid ='$model5->applid'")->queryOne(0);
			 $dt = explode("-",$model5->constrdate);
			 $model5->constrdate = $dt[2].'/'.$dt[1].'/'.$dt[0];
			 $model5->onames = $rs[0];
			 $model5->oaddress = $rs[1];
			 $model5->otelno = $rs[2];
			 $model5->oemail= $rs[3];
		   }
		
		if(isset($_POST['btnGeneral'])) 
		 {
		   if($model->load(Yii::$app->request->post()) && $model->save())
		   {
			 $none = false;
			 $_SESSION['rpid'] = $model->id;
			 $model->rid = Yii::$app->db->createCommand("SELECT rid FROM districts WHERE did = '$model->did'")->queryScalar();
			 Yii::$app->session->setFlash('hsuccess',"Inspection Report General Information Successful Saved.Please Proceed to Fill Premises Ownership by clicking Premises Ownership Tab");
		   }
		   
			   $m1 = true;
		 }
		 
		 elseif(isset($_POST['btnPInfo'])) 
		 {
		   $none = false;
		   if($model2->load(Yii::$app->request->post()) && $model2->save())
		   {
			  Yii::$app->session->setFlash('psuccess',"Inspection Report Property Information Successful Saved.Please Proceed to Fill Fire Rescue Information by clicking Fire Rescue Info Tab");
			
		   }
		   
			  $m2 = true;
		  }
		  
		 elseif(isset($_POST['btnOccupied'])) 
		 {
		   $none = false;
		   if($model5->load(Yii::$app->request->post()) && $model5->save())
		   {
			  
			  Yii::$app->session->setFlash('osuccess',"Inspection Report Business Ownership and Premise Information Successful Saved.Please Proceed to Fill Explosive available at premise by clicking Explosive Tab");
			  
			  			 $dt = explode("-",$model5->constrdate);
			 $model5->constrdate = $dt[2].'/'.$dt[1].'/'.$dt[0];
			
		   }
		   
			  $m3 = true;
		  }
		  
		  elseif(isset($_POST['btnExplosive'])) 
		  {
		   $none = false;
		   if($model6->load(Yii::$app->request->post()) && $model6->save())
		   {
			  
			  Yii::$app->session->setFlash('xsuccess',"Explosive Material Details Successful Saved.When Done to Add All Explosive Available,Click Fire Equipments Tab to Proceed");
			   $model6 = new Pexplosive;
			
		   }
		   
			  $m4 = true;
		  }
		  
		  elseif(isset($_POST['btnFFEquip'])) 
		  {
		   $none = false;
		   if($model7->load(Yii::$app->request->post()) && $model7->save())
		   {
			  
			  Yii::$app->session->setFlash('ffsuccess',"Available Fire Fighting Equipment Successful Saved.When Done to Fill All Available Fire Fighting Equipment Available,Click Fire Equipments Tab to Proceed");
			   $model7 = new Pffequip;
			
		   }
		   
			  $m5 = true;
		  }
		 elseif(isset($_POST['btnFRInfo'])) 
		 {
		   $none = false;
		   if($model3->load(Yii::$app->request->post()) && $model3->save())
		   {
			  Yii::$app->session->setFlash('fsuccess',"Inspection Report Fire Rescue Information Successful Saved.Please Complete Report by filling in Remarks and Submit.");
		   }
		   
			  $m6 = true;
		  }
		 
         elseif(isset($_POST['btnSave'])) 
		 {
		   $none = false;
		   if($model4->load(Yii::$app->request->post()) && $model4->save())
		   {
			  Yii::$app->db->createCommand("UPDATE application SET status = 'STAGE4' WHERE applid = '$rid'")->execute();
			 Yii::$app->session->setFlash('asuccess',"Fire Inspection Report Successful Saved. It has been sent to Approver for further action");
			 return $this->redirect(['mytask']);
		   }
		   
			  $m7 = true;
		  }
		
		   $q = "SELECT CONCAT(a.salutation,'. ',a.fname,' ',IFNULL(a.mname,''),' ',a.sname),CONCAT(r.name,' - ',d.name),a.paddress ";
		   $q .="FROM application a INNER JOIN districts d ON d.did = a.did INNER JOIN regions r ON r.rid = d.rid ";
		   $q .="WHERE status = 'STAGE3' AND applid =:rid";
		   $rs = Yii::$app->db->createCommand($q)->bindParam(':rid',$rid)->queryOne(0);
		    if($rs == false)
		     {
			  return $this->redirect(['management/noaccess']); 
		     }
		  $model->client = $rs[0];
		  $model->region = $rs[1];
		  $model->paddr = $rs[2];
		 
		 
		if($none)
		{
			$m1 = true;
		}
 return $this->render('tabs',['model'=>$model,'model2'=>$model2,'model3'=>$model3,'model4'=>$model4,'model5'=>$model5,'model6'=>$model6,
		                              'model7'=>$model7,'m1'=>$m1,'m2'=>$m2,'m3'=>$m3,'m4'=>$m4,'m5'=>$m5,'m6'=>$m6,'m7'=>$m7
		                              ]);
									 
        //return $this->render('_fattend', ['model' => $model]);
	}
	
	
	public function getExplosive()
	{
		$apid = $_SESSION['rid'];
		$rst = Yii::$app->db->createCommand("SELECT material,maxq,mos,quantity,id FROM pexplosive WHERE applid = '$apid'")->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>Materials</th><th>Max. Quantity</th><th>Method of Usage</th><th>Max Quantity Liable to be exposed at a time</th><th>Edit</th></tr>";
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
	public function getFFequip()
	{
		$apid = $_SESSION['rid'];
		$q = "SELECT e.name,f.quantity,f.location,f.maintained,f.id FROM pffequip f INNER JOIN equipments e ON e.id = f.equipid ";
		$q .="WHERE f.applid = '$apid'";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-striped table-bordered'>";
		    $tbTr .="<tr><th>SN</th><th>Nature of Equipment</th><th>Number Provided</th><th>Where Installed</th><th>Regurlaly Maintained?</th><th>Edit</th></tr>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['site/editffequip','id'=>$rs[4]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	public function RPFilled()
	{
		$apid = $_SESSION['rid'];
		$yr = Yii::$app->user->identity->currYear;
		
		$rs = Yii::$app->db->createCommand("SELECT COUNT(applid) FROM reports WHERE instage = 'STRPT' and foryear ='$yr' AND applid = '$apid'")->queryScalar();
		if($rs > 0)
		{
			return true;
		}
		return false;
	}
	
	public function RFilled()
	{
		$apid = $_SESSION['rid'];
		$yr = Yii::$app->user->identity->currYear;
		
		$rs = Yii::$app->db->createCommand("SELECT COUNT(applid) FROM reports WHERE instage IN('FIRE','STRPT') and foryear ='$yr' AND applid = '$apid'")->queryScalar();
		if($rs > 0)
		{
			return true;
		}
		return false;
	}
	
	
	
	public function FFFilled()
	{
		$apid = $_SESSION['rid'];
		$yr = Yii::$app->user->identity->currYear;
		$q ="SELECT COUNT(applid) FROM reports WHERE instage IN ('FFEQUIP','FIRE','STRPT') ";
		$q .="and foryear ='$yr' AND applid = '$apid'";
		
		$rs = Yii::$app->db->createCommand($q)->queryScalar();
		
		if($rs > 0)
		{
			return true;
		}
		return false;
	}
	
	
	public function EXFilled()
	{
		$apid = $_SESSION['rid'];
		$yr = Yii::$app->user->identity->currYear;
		$q ="SELECT COUNT(applid) FROM reports WHERE instage IN ('EXPLOSIVE','FFEQUIP','FIRE','STRPT') ";
		$q .="and foryear ='$yr' AND applid = '$apid'";
		
		$rs = Yii::$app->db->createCommand($q)->queryScalar();
		
		if($rs > 0)
		{
			return true;
		}
		return false;
	}
	
	public function PRFilled()
	{
		$apid = $_SESSION['rid'];
		$yr = Yii::$app->user->identity->currYear;
		$q ="SELECT COUNT(applid) FROM reports WHERE instage IN ('OWNERSHIP','EXPLOSIVE','FFEQUIP','FIRE','STRPT') ";
		$q .="and foryear ='$yr' AND applid = '$apid'";
		
		$rs = Yii::$app->db->createCommand($q)->queryScalar();
		
		if($rs > 0)
		{
			return true;
		}
		return false;
	}
	
	public function PFilled()
	{
		$apid = $_SESSION['rid'];
		$yr = Yii::$app->user->identity->currYear;
		$q ="SELECT COUNT(applid) FROM reports WHERE instage IN ('FIRE','PROPERTY','OWNERSHIP','EXPLOSIVE','FFEQUIP','STRPT'
		) ";
		$q .="and foryear ='$yr' AND applid = '$apid'";
		
		$rs = Yii::$app->db->createCommand($q)->queryScalar();
		
		if($rs > 0)
		{
			return true;
		}
		return false;
	}
	
	protected function loadMod5($id)
    {
       $ipo = Yii::$app->db->createCommand("SELECT COUNT(applid) FROM poccupied WHERE applid = '$id'")->queryScalar(); 
       if($ipo > 0)
	   {		   
	   if (($model = Poccupied::findBySql("SELECT * FROM poccupied WHERE applid ='$id'")->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
	   }
	   else
	   {
		   $model = new Poccupied;
		   return $model;
	   }
    }
	
	protected function loadRpt($id)
    {
        if (($model = Reports::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	protected function loadRpt2($id)
    {
        if (($model = Report2::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	protected function loadRpt3($id)
    {
        if (($model = Report3::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	protected function loadRpt4($id)
    {
        if (($model = Report4::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function actionGetdistricts()
	{
		$data = [];
         if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $nat = $parents[0];
           	   $data = Yii::$app->db->createCommand("SELECT did as id,name FROM districts WHERE rid ='$nat' ORDER BY name")->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
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
	
	public function getInfo($apid)
	{
		$tbTr ="<table class='table table-bordered'><tr bgcolor=grey ><th colspan=4>Applicant Information</th></tr>";
		$q = "SELECT CONCAT(salutation,' ',fname,' ',IFNULL(mname,''),' ',sname),telno,onbehalf,capacity FROM application WHERE applid ='$apid'";
		$r = Yii::$app->db->createCommand($q)->queryOne(0);
		
		
		$tbTr .="<tr><td><b>Applicant Name</b></td><td>$r[0]</td><td><b>Telephone</b></td><td>$r[1]</td></tr>";
		$tbTr .="<tr><td><b>Application Onbehalf</b></td><td>$r[2]</td><td><b>Applied in Capacity of</b></td><td>$r[3]</td></tr>";
		$tbTr .="</table>";
		
		return $tbTr;
	}
	public function getCInfo($apid)
	{
		
		
		
		$q = "SELECT i.invoicenum,i.tamount,r.receiptno,DATE_FORMAT(r.cdate,'%d/%m/%Y'),r.amount,";
		$q .="DATE_FORMAT(DATE_ADD(r.cdate,INTERVAL 1 YEAR),'%d/%m/%Y') ";
		$q .="FROM invoice i INNER JOIN receipts r ON i.invoicenum = r.invoicenum WHERE i.applid = '$apid'";
		$r = Yii::$app->db->createCommand($q)->queryOne(0);
		$tbTr ="<table class='table table-bordered'>";
		$tbTr .="<tr><td><b>Invoice #</b></td><td>$r[0]</td><td><b>Amount</b></td><td>".number_format($r[1],2)."</td></tr>";
		$tbTr .="<tr><td><b>Receipt #</b></td><td>$r[2]</td><td colspan=2><b>Receipt Date:</b> $r[3] <b>Receipt Amount:&nbsp;&nbsp;</b>".number_format($r[4],2)."</td></tr>";
		$tbTr .="<tr><td><b>Certificate Start Date</b></td><td>$r[3]</td><td><b>Valid Up to</b></td><td>$r[5]</td></tr>";
		$tbTr .="</table>";
		
		return $tbTr;
	}

    public function actionProfile() {

        return $this->render('client/show');
    }
	
	public function actionBcode()
	{
		return $this->render('bcode');
	}
	
	public function actionAdduser() 
	{
        if(!FireRoles::isAdmin())
		  {
			  return $this->redirect(['management/noaccess']);
		  }
		
		$model = new Users;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  $q = "INSERT INTO userrole(rid,userid,fdate,tdate) VALUES('$model->rid','$model->userid',CURDATE(),'2038-09-14')";
		  Yii::$app->db->createCommand($q)->execute();
		  
		  Yii::$app->session->setFlash('usuccess',"System User Successful Registered. Please tell this user to change his/her password immedialtely after login.");
		  return $this->refresh();
        }
		
        return $this->render('_fuser',['model'=>$model,'tbUser'=>$this->getUsers()]);
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
