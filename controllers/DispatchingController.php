<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\data\SqlDataProvider;
use yii\filters\VerbFilter;
use app\models\Rct;
use app\models\Release;
use app\models\Receipts;
use app\models\FrostanRoles;
use yii\helpers\Html;
use yii\helpers\Json;
use kartik\mpdf\Pdf;

class DispatchingController extends Controller
{
	public $menu = 'dispatchingmenu';

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

    function getCode() 
	{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 5; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
	 
	
	///////////////////////////////////////////////////////////////////////////////////////////
	
    public function actionIndex()
    {
        
		if(!FrostanRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		  $model = new Release;
		  $tbH = '';
		  $tbD ='';
		if(isset($_POST['btnSName']))
			{
				if($model->load(Yii::$app->request->post()) && $model->validate())
				{
				$rno = $model->rlno;
				$rctno = Yii::$app->db->createCommand("SELECT rctno FROM orderitems WHERE rlno ='$rno' LIMIT 1")->queryScalar();
			    $qpr ="SELECT c.name,CONCAT('+',c.pcode,c.phone),c.ctype FROM clients c INNER JOIN clreceipts r ON ";
                $qpr .="c.cid = r.cid WHERE r.rctno ='$rctno'";
		        $r = Yii::$app->db->createCommand($qpr)->queryOne(false);
				
				$tbH ="<table class='table table-bordered table-gray'><thead><tr><th>Client Name</th><th>Telephone Number</th>";
			    $tbH .="<th>Client Type</th><th>Release #</th></tr></thead>";
			    $tbH .="<tr><td><b>$r[0]</b></td><td><b>$r[1]</b></td><td><b>$r[2]</b></td><td><b>$rno</b></td></tr>";
 
				 
				$tbD = "<table class='table table-bordered'><tr><th>SN</th><th>Control No</th><th>Item Type</th>";
		        $tbD .="<th>Items</th><th>CBM</th></tr>";
		   
		        $q = "SELECT g.name,g.rate,oi.hascbm,oi.cbm,oi.price,oi.cno,oi.iid FROM orderitems oi INNER JOIN itemgroup g ON ";
		        $q .="g.gid = oi.gid WHERE oi.rlno ='$rno'";
		        $rst2 = Yii::$app->db->createCommand($q)->queryAll(false);
		          $i = 1;
				  foreach($rst2 as $rs)
		          {
					  if($rs[2] == 'Y')
					  {
						 
						$cbm = $rs[3];
						
					  }
					  else
					  {
						
						 $cbm = 'NA'; 
					  }
					  $cnt = 1;
					$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$rs[5]'")->queryScalar();
					if($ccn)
					{
						$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
					}
					$dl = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$rs[5]'")->queryAll(false);
					  $it = '';
					  foreach($dl as $r)
					  {
						  $it .= '<b>'.$r[0].'</b>: Items : '.$r[2].' : Descr : '.$r[3].' , ';
						
					  }
					$it = rtrim($it,' , ');
					  
					 
			         $tbD .="<tr><td>$i</td><td>$rs[5]</td><td>$rs[0]</td><td>$it</td><td>$cbm</td></tr>";
					 $i++; 
				  }
			  $tbD .="<tr><td colspan=5 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Click To Deliver',['dispatching/clear','rlno'=>$rno],['data'=>['method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				 }
				 
				 $tbD .="</table>";
				}
		
			  
		return $this->render('index',['tbH'=>$tbH,'tbD'=>$tbD,'model'=>$model]);
		
    }
	
	public function actionClear($rlno)
	{
		$id = Yii::$app->user->id;
		Yii::$app->db->createCommand("UPDATE orderitems SET cleared ='Y',clby ='$id',cldate =NOW() WHERE rlno ='$rlno'")->execute();
		Yii::$app->session->setFlash('clsuccess',"Items with Release #: <b>$rlno</b> Has Been Successful Delivered");
		
		return $this->render('vwcl');
	}
	
	
	public function actionPrintrro($rctno)
	{
	  date_default_timezone_set('Africa/Nairobi');
	 // $this->layout = 'min-master';
	  $Total = 0;
      $VAT = 0;
      $BigTotal = 0;
	  $BigTotalUSD = 0;
	  
      putenv("TZ=Africa/Nairobi");
	    $erate = Yii::$app->user->identity->rate;
        $conn = Yii::$app->db;
        $qci ="SELECT cname,paddress,box,telephone,fax,mob,region,vat,tin,email FROM companyinfo WHERE cid =3";
		$cd = $conn->createCommand($qci)->queryOne(false);
		 
		 $qpr ="SELECT c.name,c.ctype,r.amt,r.inusd,r.erate,r.hasvat,r.vat,r.vatinusd,DATE_FORMAT(r.cdate,'%d/%m/%Y'),  ";
		 $qpr .="DATE_FORMAT(r.cby,'%H:%i:%s'),r.cby FROM clreceipts r INNER JOIN clients c ON ";
         $qpr .="c.cid = r.cid WHERE r.rctno =:rno";
		 $rs = $conn->createCommand($qpr)->bindParam(':rno',$rctno)->queryOne(false);
		 
		 $TAmount = $rs[2];
			$TAmountUSD = $rs[3];
			$ERate = $rs[4];
			$VAT = $rs[6]; 
			$VatUSD = $rs[7]; 
		    $pmode = "CASH";
		 
		 $fn = $conn->createCommand("SELECT CONCAT(fname,' ',mname,' ',sname) FROM employees WHERE empid = '$rs[10]'")->queryScalar();
		 
		/*$tbBC ="<table cellspacing=0 width=100% border=0 >";
        $tbBC .="<tr><td>&nbsp;&nbsp;&nbsp;<h1>:::.PROFORMA INVOICE.:::</h1></td><td align=right>";
		$tbBC .="<img src=".Yii::app()->createAbsoluteUrl('barcodegenerator/generatebarcode',array('code'=>$ino))."><br />&nbsp;</td>";
		$tbBC .="</tr></table>"; */
		
        $tbIheader ="<table cellpadding=1 border=1 cellspacing=0 width=70%>";
		$tbIheader .="<tr><td rowspan=5 class=theinv><img src=../img/socean.png width=250 height=80 /></td>";
        $tbIheader .="<td colspan=2 class=theinv><b><font size=+1>$cd[0]</font></b></td></tr>";
        $tbIheader .="<tr><td class=theinv>$cd[1]</td><td class=theinv>VAT:&nbsp;<b>$cd[7]</b></td></tr>";
        $tbIheader .="<tr><td class=theinv>P.O. Box:&nbsp;$cd[2]&nbsp;&nbsp;$cd[6]</td><td class=theinv>TIN:&nbsp;<b>$cd[8]</b></td></tr>";
	    $tbIheader .="<tr><td class=theinv>Telephone:&nbsp;$cd[3]&nbsp;&nbsp;Fax:&nbsp;$cd[4]</td>";
	    $tbIheader .="<td class=theinv>USD EXCHANGE RATE:&nbsp;<b>".number_format($rs[4],2)."</b></td></tr>";
		$tbIheader .="<tr><td class=theinv>Mobile:&nbsp;$cd[5]</td><td class=theinv>Email:&nbsp;$cd[9]</td></tr></table>";
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Release Order
		$tbIheader2 ="<table cellpadding=1 border=1 cellspacing=0 width=70%>";
		$tbIheader2 .="<tr><td rowspan=5 class=theinv><img src=../img/socean.png width=250 height=80 /></td>";
        $tbIheader2 .="<td colspan=2 class=theinv><b><font size=+1>$cd[0]</font></b></td></tr>";
        $tbIheader2 .="<tr><td class=theinv>$cd[1]</td><td class=theinv>VAT:&nbsp;<b>$cd[7]</b></td></tr>";
        $tbIheader2 .="<tr><td class=theinv>P.O. Box:&nbsp;$cd[2]&nbsp;&nbsp;$cd[6]</td><td class=theinv>TIN:&nbsp;<b>$cd[8]</b></td></tr>";
	    $tbIheader2 .="<tr><td class=theinv>Telephone:&nbsp;$cd[3]&nbsp;&nbsp;Fax:&nbsp;$cd[4]</td>";
	    $tbIheader2 .="<td class=theinv>USD EXCHANGE RATE:&nbsp;<b>".number_format($rs[4],2)."</b></td></tr>";
		$tbIheader2 .="<tr><td class=theinv>Mobile:&nbsp;$cd[5]</td><td class=theinv>Email:&nbsp;$cd[9]</td></tr></table>";
		
		 $ro = $conn->createCommand("SELECT rlno FROM orderitems WHERE rctno =:rno LIMIT 1")->bindParam(':rno',$rctno)->queryScalar();
			$tbCust2 ="<table cellpadding=1 border=1 cellspacing=0 width=70%>";
            $tbCust2 .="<tr style='border: 1px solid #000000;'><td align=right class=theinv>To :</td><td class=theinv><b>$rs[0]</b></td><td align=right class=theinv>Date :</td><td class=theinv><b>$rs[8]&nbsp;$rs[9]</b></td></tr>";
 
            $tbCust2 .="<tr><td align=right class=theinv>Release Order # :</td><td class=theinv><b>$ro</b></td>";
            $tbCust2 .="<td align=right class=theinv>Currency :</td><td class=theinv><b>TZS | USD</b></td></tr></table>";
			
			
			$tbInvoice2 ="<table cellpadding=1 border=1 cellspacing=0 width=70%><tr style='border: 1px solid #000000;'><th colspan=4 class=theinv>Items Description</th></tr>";
           $tbInvoice2 .= "<tr style='border: 1px solid #000000;' align=left><th>SN</th><th>Control No</th><th>Item Name</th>";
		   $tbInvoice2 .="<th>CBM</th></tr>";
		   
		   $q = "SELECT i.name,i.rate,oi.hascbm,oi.cbm,oi.price,oi.cno FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid ";
		   $q .="WHERE oi.rctno ='$rctno'";
		   $rst2 = $conn->createCommand($q)->queryAll(false);
		          $i = 1;
				  foreach($rst2 as $rs)
		          {
					  if($rs[2] == 'Y')
					  {
						 
						$cbm = $rs[3];
						
					  }
					  else
					  {
						
						 $cbm = 'NA'; 
					  }
					  
					 
			         $tbInvoice2 .="<tr style='border: 1px solid #000000;'><td>$i</td><td>$rs[5]</td><td>$rs[0]</td><td>$cbm</td></tr>";
					// $tbInvoice2 .="<td>".number_format($pricet,2)."</td><td>".number_format($price,2)."</td><tr>";
				
					 $i++; 
				  }
			   /*$tbInvoice2 .="<tr><td colspan=5 align=right><b>VAT - 18%</b></td>";
			   $tbInvoice2 .="<td><b>".number_format($VAT,2)."</b></td><td><b>".number_format($VatUSD,2)."</b></td></tr>";
			   $tbInvoice2 .="<tr><td colspan=5 align=right><b>Total</b></td>";
			   $tbInvoice2 .="<td><b>".number_format($TAmount,2)."</b></td><td><b>".number_format($TAmountUSD,2)."</b></td></tr>";*/
			   $tbInvoice2  .="<tr><td colspan=4 class=theinv><br />&nbsp;<br />&nbsp;<center>Printed by&nbsp;<b>$fn</b>&nbsp;&nbsp;&nbsp;";
               $tbInvoice2  .="Printed Date:&nbsp;<b>".date("d/m/Y")."</b>&nbsp;&nbsp;&nbsp;Time:&nbsp;<b>".date("h:i:s")."</b></center></td>";
               $tbInvoice2  .="</tr></table>";	
		
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
		
            $tbCust ="<table cellpadding=1 border=1 cellspacing=0 width=70%>";
            $tbCust .="<tr style='border: 1px solid #000000;'><td align=right class=theinv>To :</td><td class=theinv><b>$rs[0]</b></td><td align=right class=theinv>Date :</td><td class=theinv><b>$rs[8]&nbsp;$rs[9]</b></td></tr>";
 
            $tbCust .="<tr style='border: 1px solid #000000;'><td align=right>Receipt # :</td><td class=theinv><b>$rctno</b></td>";
            $tbCust .="<td align=right class=theinv>Currency :</td><td class=theinv><b>TZS | USD</b></td></tr></table>";
 
           $tbInvoice ="<table cellpadding=1 border=1 cellspacing=0 width=70%><tr style='border: 1px solid #000000;'><th colspan=7 class=theinv>Receipt Description</th></tr>";
           $tbInvoice .= "<tr style='border: 1px solid #000000;' align=left><th>SN</th><th>Control No</th><th>Item Name</th>";
		   $tbInvoice .="<th>CBM</th><th>Calculation</th><th>Price (TZS)</th><th>Price (USD)</th></tr>";
		   
		   $q = "SELECT i.name,i.rate,oi.hascbm,oi.cbm,oi.price,oi.cno FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid ";
		   $q .="WHERE oi.rctno ='$rctno'";
		   $rst = $conn->createCommand($q)->queryAll(false);
		          $i = 1;
				  foreach($rst as $rs)
		          {
					  if($rs[2] == 'Y')
					  {
						$price = $rs[4];  
						$cbm = $rs[3];
						$calc = $rs[3]. ' X '. $rs[1];
					  }
					  else
					  {
						 $price = $rs[4];  
						 $calc = '1 X '. $rs[1];
						 $cbm = 'NA'; 
					  }
					  $pricet = $price * $erate;
					  $TZS = $TZS + $pricet;
					 
			  $tbInvoice .="<tr style='border: 1px solid #000000;'><td>$i</td><td>$rs[5]</td><td>$rs[0]</td><td>$cbm</td><td>$calc</td>";
					 $tbInvoice .="<td>".number_format($pricet,2)."</td><td>".number_format($price,2)."</td><tr>";
				
					 $i++; 
				  }
			   $tbInvoice .="<tr style='border: 1px solid #000000;'><td colspan=5 align=right><b>VAT - 18%</b></td>";
			   $tbInvoice .="<td><b>".number_format($VAT,2)."</b></td><td><b>".number_format($VatUSD,2)."</b></td></tr>";
			   $tbInvoice .="<tr><td colspan=5 align=right><b>Total</b></td>";
			   $tbInvoice .="<td><b>".number_format($TAmount,2)."</b></td><td><b>".number_format($TAmountUSD,2)."</b></td></tr>";
			   $tbInvoice  .="<tr style='border: 1px solid #000000;'><td colspan=7 class=theinv><br />&nbsp;<br />&nbsp;<center>Printed by&nbsp;<b>$fn</b>&nbsp;&nbsp;&nbsp;";
               $tbInvoice  .="Printed Date:&nbsp;<b>".date("d/m/Y")."</b>&nbsp;&nbsp;&nbsp;Time:&nbsp;<b>".date("h:i:s")."</b></center></td>";
               $tbInvoice  .="</tr></table>";	
		       
		return $this->renderAjax('vwrct',['tbHeader'=>$tbIheader,'tbCust'=>$tbCust,'tbInv'=>$tbInvoice,
		                                 'tbHeader2'=>$tbIheader2,'tbCust2'=>$tbCust2,'tbInv2'=>$tbInvoice2]); 
	}
	
	public function getCDet($cid)
	{
		 
		$q = "SELECT name,address,CONCAT('+',pcode,phone),ctype FROM clients WHERE cid = '$cid'";
		if($fsq == 'Y')
		{
			$odate = Yii::$app->db->createCommand("SELECT DATE_FORMAT(orderdate,'%d/%m/%Y') FROM orders WHERE cid ='$cid' AND orderno ='$oid'")->queryScalar();
		}
		$rs = Yii::$app->db->createCommand($q)->queryOne(0);
		
		$tbData = "<table class='table table-bordered table-gray'><thead><tr><th>CUSTOMER NAME</th><th>ADDRESS</th>";
		$tbData .= "<th>PHONE</th><th>CUSTOMER TYPE</th><th><b>Exchange Rate</b></th></thead></tr></thead>";
		
		if($rs)
		{
			$tz = Yii::$app->user->identity->rate;
			$tbData .="<tr><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
			$tbData .="<td><font color=blue><b>1USD = ".number_format($tz,2) ."TSH</b></font></td></tr></table>";
			return $tbData;
		}
		else
		{
			return $tbData .= "<tr><td><b>Invalid Request</td></tr></table>";
		}
		
		
	}
	
	public function actionRmitems($cid)
	{
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['receipts']);
	}
	
	public function getSQI()
	{
	   $data = [];
	   $cid = $_SESSION['cid'];
		  
		  $conn = Yii::$app->db;
		  if($_SESSION['JobItems'])
		  {
		    foreach($_SESSION['JobItems'] as $ckey=>$val) 
			{
			 $Aselected .=$ckey.",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
		    $q ="SELECT oi.cno,CONCAT(oi.cno,' - ',i.name) FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid INNER JOIN orders o ";
	        $q .="ON o.orderno = oi.orderno WHERE o.cid = '$cid' AND oi.shipped ='Y' AND oi.paid ='N' AND NOT oi.cno IN('$RS')"; 
		  }
		  else
		  {
			 $q ="SELECT oi.cno,CONCAT(oi.cno,' - ',i.name) FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid INNER JOIN orders o ";
	         $q .="ON o.orderno = oi.orderno WHERE o.cid = '$cid' AND oi.shipped ='Y' AND oi.paid ='N'";  
		  }
		$rslt = $conn->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	      $data[$rs[0]] = $rs[1];
	     }

		 return $data;	
	}
	
	
	public function actionNoaccess()
	{
		return $this->render('noaccess');
	}


}
