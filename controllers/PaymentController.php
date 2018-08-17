<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\data\SqlDataProvider;
use yii\filters\VerbFilter;
use app\models\Rct;
use app\models\Receipts;
use app\models\OPayments;
use app\models\FrostanRoles;
use yii\helpers\Html;
use yii\helpers\Json;
use kartik\mpdf\Pdf;

class PaymentController extends Controller
{
	public $menu = 'paymentmenu';

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
        unset($_SESSION['cid']);
		unset($_SESSION['JobItems']);
		unset($_SESSION['JobFees']);
		if(!FrostanRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		  $model = new Rct;
		if(isset($_POST['btnSName']))
			{
				$model->load(Yii::$app->request->post());
				$cn = explode(":",$model->name);
			$q2 ="SELECT @s:=@s+1 as Sn,oi.cno as 'Control Number',oi.iid,CASE oi.hascbm WHEN 'Y' THEN oi.cbm ELSE 'NA' END ";
		    $q2 .="as 'CBM',FORMAT(price,2) as 'Price USD',c.name as 'Client',CONCAT('+',pcode,phone) as 'Phone Number',o.cid,oi.containerno as 'Container#',oi.blno as 'BL #' ";
		    $q2 .="FROM orderitems oi INNER JOIN itemgroup i ON i.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN ";
		    $q2 .="clients c ON c.cid = o.cid,(SELECT @s:=0) AS s WHERE oi.hasbl = 'Y' AND oi.paid ='N' AND ";
		    $q2 .="LOWER(oi.cno) = LOWER('$cn[0]')";
		
		        
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM orderitems WHERE LOWER(cno) LIKE LOWER('%$cn[0]%')")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Client Name','Client Type','Registered Date'],],
							'pagination'=>[
							               'pageSize'=>10,
	                                      ],
						    ]);	
      
         	  
		return $this->render('index',['dataProvider'=>$dataProvider,'model'=>$model]);
		}
		else
		{
			
						
		$q2 ="SELECT @s:=@s+1 as Sn,oi.cno as 'Control Number',oi.iid,CASE oi.hascbm WHEN 'Y' THEN oi.cbm ELSE 'NA' END ";
		$q2 .="as 'CBM',FORMAT(price,2) as 'Price USD',c.name as 'Client',CONCAT('+',pcode,phone) as 'Phone Number',o.cid,oi.containerno as 'Container#',oi.blno as 'BL #' ";
		$q2 .="FROM orderitems oi INNER JOIN itemgroup i ON i.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN ";
		$q2 .="clients c ON c.cid = o.cid,(SELECT @s:=0) AS s WHERE oi.hasbl = 'Y' AND oi.paid ='N'";

		
		
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM clients")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Client Name','Client Type','Registered Date'],],
							'pagination'=>[
							               'pageSize'=>10,
	                                      ],
						    ]);	
      
         	  
		return $this->render('index',['dataProvider'=>$dataProvider,'model'=>$model]);
		}
    }
	
	public function actionStopay($q = null) 
	 {
		$q = strtolower($q);

		
	   $qd ="SELECT CONCAT(oi.cno,':',c.name,':',CONCAT('+',pcode,phone)) FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno ";
	   $qd .="INNER JOIN clients c ON o.cid = c.cid WHERE oi.hasbl = 'Y' AND oi.paid = 'N' AND LOWER(oi.cno) LIKE '%$q%' UNION ";
	   
	   $qd .="SELECT CONCAT(oi.cno,':',c.name,':',CONCAT('+',pcode,phone)) FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno ";
	   $qd .="INNER JOIN clients c ON o.cid = c.cid WHERE oi.hasbl = 'Y' AND oi.paid = 'N' AND ";
	   $qd .="LOWER(c.name) LIKE LOWER('%$q%') UNION ";
	 
	   $qd .="SELECT CONCAT(oi.cno,':',c.name,':',CONCAT('+',pcode,phone)) FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno ";
	   $qd .="INNER JOIN clients c ON o.cid = c.cid WHERE oi.hasbl = 'Y' AND oi.paid = 'N' AND ";
	   $qd .="CONCAT('+',pcode,phone) LIKE '%$q%'";
	
		
		$data = Yii::$app->db->createCommand($qd)->queryAll(false);
		$out = [];
		foreach ($data as $d) {
			$out[] = ['value' => $d[0]];
		}
		echo Json::encode($out);
    }
	
	
	public function actionPrct($cid)
	{
		$_SESSION['cid'] = $cid;
		return $this->redirect(['receipts']);
	}
	
	public function actionReceipts()
	{
		$cid = $_SESSION['cid'];
		$erate = Yii::$app->user->identity->rate;
		if(!FrostanRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }

		  $model = new Receipts;
		  $model2 = new OPayments;
		  $hasTotal = false;
		  
		  if(isset($_POST['btnAdd2']))
		  {
		    if(isset($_POST['OPayments']))
		     {
			   
			   $model2->load(Yii::$app->request->post());
			   if($model2->validate())
			   {
					if($model2->paidin2 == 'USD')
			         {
				        $inusd2 = $model2->amt;
				        $model2->amt = $inusd2 * $erate;
			         }
			        else
			        {
				      $inusd2 = $model2->amt / $erate;
			        }
					$_SESSION['JobFees'][$model2->fid.':'.$model2->amt.':'.$inusd2]++;  //add this chassis number to the session variable  
					
					$model2->paidin2 ='';
					$model2->amt ='';
			   } 
		     }
		 }
          
		 if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				//add this item to the session variable 
                $_SESSION['JobItems'][$model->iid]++;  
			    $this->refresh(); 
				
		   }
          }
		  
		   $i = 1;
		   $Total = $TSH = 0;
		   $erate = Yii::$app->user->identity->rate;
		   
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Control No</th><th>Item Type</th>";
		   $tbInvoice .="<th>Items</th><th>CBM</th><th>Calculation</th><th>Price (TZS)</th><th>Price (USD)</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					 // $ck = explode(":",$ckey);
					  $qi = "SELECT g.name,g.rate,oi.hascbm,oi.cbm,oi.price,oi.iid,oi.cno FROM orderitems oi INNER JOIN ";
                      $qi .="itemgroup g ON g.gid = oi.gid WHERE oi.id ='$ckey'";		
					  $pr = Yii::$app->db->createCommand($qi)->queryOne(0);
					  if($pr[2] == 'Y')
					  {
						$price = $pr[4];  
						$cbm = $pr[3];
						$calc = $pr[3]. ' X '. $pr[1];
					  }
					  else
					  {
						 $price = $pr[4];  
						 $calc = '1 X '. $pr[1];
						 $cbm = 'NA'; 
					  }
					  $cnt = 1;
						$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$pr[6]'")->queryScalar();
						if($ccn)
						{
							$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
						}
						$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$pr[6]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $r)
					  {
						  $it .= $r[0].': Items : '.$r[2].' : Descr : '.$r[3].' , ';
						
					  }
					$it = rtrim($it,' , ');
					  
					  
					  $pricet = $price * $erate;
					  $Total = $Total + $price;
					  $TZS = $TZS + $pricet;
					 
			 $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['payment/rmitems','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$ckey</td><td>$pr[0]</td><td>$it</td><td>$cbm</td><td>$calc</td>";
					 $tbInvoice .="<td>".number_format($pricet,2)."</td><td>".number_format($price,2)."</td><tr>";
				
					 $i++; 
				  }
				   if($_SESSION['JobFees'])
				   {
				     foreach($_SESSION['JobFees'] as $ckey=>$val)
		             {
					   $ck = explode(":",$ckey);
					   $qc ="SELECT name,hasvat FROM fees WHERE feeid ='$ck[0]'";
				       $rs = Yii::$app->db->createCommand($qc)->queryOne(false);
					   
					    $t2 = $t2 + $ck[1];
						$TZS = $TZS + $ck[1];
					    $Total = $Total + $ck[2];
						
						
				        $tbInvoice .="<tr><td colspan=4 align=right><b>".Html::a('Remove',['payment/rmcno2','cno'=>$ckey])."</b></td>";
					    $tbInvoice .="<td colspan =3 align=right>$rs[0]</td><td>".number_format($ck[1],2)."</td><td>".number_format($ck[2],2)."</td></tr>";
				    }
					
				  }
			   $tbInvoice .="<tr><td colspan=7 align=right><b>Total</b></td>";
			   $tbInvoice .="<td><b>".number_format($TZS,2)."</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
			  $tbInvoice .="<tr><td colspan=9 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Ok.Generate Receipt',['payment/crrct'],['data'=>['confirm'=>'Proceed To Generate Receipt?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				 }
				 
				 $tbInvoice .="</table>";
		         return $this->render('_freceipt',['model'=>$model,'model2'=>$model2,'tbDet'=>$this->getCDet($cid),
		                               'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
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
	
	public function actionCrrct()
	{
                  				
		  if($_SESSION['JobItems'])
		   {
			   $erate = Yii::$app->user->identity->rate;
			   $id = Yii::$app->user->id;
			   $cid = $_SESSION['cid'];
			   
			   $num = Yii::$app->db->createCommand("SELECT rctnumber FROM accode")->queryScalar();
		       Yii::$app->db->createCommand("UPDATE accode SET rctnumber = rctnumber + 1")->execute();
		       $rctno = str_shuffle($this->getCode().$num);
			   
			   $num2 = Yii::$app->db->createCommand("SELECT rlnumber FROM accode")->queryScalar();
		       Yii::$app->db->createCommand("UPDATE accode SET rlnumber = rlnumber + 1")->execute();
		       $rlno = str_shuffle($this->getCode().$num2);
			   $ttusd = $ttzs = 0;
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				     
		                $amt = Yii::$app->db->createCommand("SELECT price FROM orderitems WHERE id = '$ckey'")->queryScalar();
						$ttusd = $ttusd + $amt;
						$ttzs = $ttzs + ($amt * $erate);
						
                        $qu ="UPDATE orderitems SET paid = 'Y',rctno = '$rctno',rlno = '$rlno',rlby = '$id',rldate = NOW() ";
						$qu .="WHERE id ='$ckey'";
		                Yii::$app->db->createCommand($qu)->execute(); 
		        }
			if($_SESSION['JobFees'])
		     {
				foreach($_SESSION['JobFees'] as $ckey=>$val)
		             {
					   $ck = explode(":",$ckey);
					   $qc ="SELECT name,hasvat FROM fees WHERE feeid ='$ck[0]'";
				       $rs = Yii::$app->db->createCommand($qc)->queryOne(false);
					 
					    $ttusd = $ttusd + $ck[2];
						$ttzs = $ttzs + $ck[1];
						
				        $qhc ="INSERT INTO receiptitem(rctno,feeid,amount,inusd,erate,status,regby,regdate)";
                        $qhc .=" VALUES('$rctno','$ck[0]','$ck[1]','$ck[2]','$erate','N','$id',NOW())";
						 Yii::$app->db->createCommand($qhc)->execute();
				    }
			 }    
				        $vat = $ttzs * 0.18;
						$vatusd = $ttusd * 0.18;
						$qr ="INSERT INTO clreceipts(rctno,cid,amt,inusd,erate,hasvat,vat,vatinusd,cby,cdate) VALUES ";
						$qr .="('$rctno','$cid','$ttzs','$ttusd','$erate','Y','$vat','$vatusd','$id',NOW())";
		                Yii::$app->db->createCommand($qr)->execute(); 	             
					 
		  }
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['JobFees']);
						unset($_SESSION['cid']);  //remove ALL chassis number to the session variable
						
			
			 $this->redirect(['payment/printrro','rctno'=>$rctno]);
		
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
			
			
			$tbInvoice2 ="<table cellpadding=1 border=1 cellspacing=0 width=70%><tr style='border: 1px solid #000000;'><th colspan=5 class=theinv>Items Description</th></tr>";
           $tbInvoice2 .= "<tr style='border: 1px solid #000000;' align=left><th>SN</th><th>Control No</th><th>Item Type</th>";
		   $tbInvoice2 .="<th>Items</th><th>CBM</th></tr>";
		   
		   $q = "SELECT g.name,g.rate,oi.hascbm,oi.cbm,oi.price,oi.cno,oi.iid FROM orderitems oi INNER JOIN itemgroup g ON  ";
		   $q .="g.gid = oi.gid WHERE oi.rctno ='$rctno'";
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
					  
					  $cnt = 1;
					$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$rs[5]'")->queryScalar();
					if($ccn)
					{
						$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
					}
					$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$rs[5]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $r)
					  {
						  $it .= '<b>'.$r[0].'</b>: Items : '.$r[2].' : Descr : '.$r[3].' , ';
						
					  }
					$it = rtrim($it,' , ');
					  
					 
			         $tbInvoice2 .="<tr style='border: 1px solid #000000;'><td>$i</td><td>$rs[5]</td><td>$rs[0]</td><td>$it</td>";
					$tbInvoice2 .="<td>$cbm</td></tr>";
				
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
		   
		   $q = "SELECT g.name,g.rate,oi.hascbm,oi.cbm,oi.price,oi.cno,oi.iid FROM orderitems oi INNER JOIN itemgroup g ON ";
		   $q .="g.gid = oi.gid  WHERE oi.rctno ='$rctno'";
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
					
					  $pricet = $price * $erate;
					  $TZS = $TZS + $pricet;
					 
			  $tbInvoice .="<tr style='border: 1px solid #000000;'><td>$i</td><td>$rs[5]</td><td>$it</td><td>$cbm</td><td>$calc</td>";
					 $tbInvoice .="<td>".number_format($pricet,2)."</td><td>".number_format($price,2)."</td><tr>";
				
					 $i++; 
				  }
				  
				  //hapa kuweka additional charges kesho
				  $qp2 ="SELECT f.name,r.amount,r.inusd FROM fees f INNER JOIN receiptitem r ON ";
				   $qp2 .="f.feeid = r.feeid WHERE r.rctno ='$rctno' ORDER BY r.feeid DESC";
				   $rst2 = Yii::$app->db->createCommand($qp2)->queryAll(false);
				  
				  foreach($rst2 as $rs)
		          {
					 
				 $tbInvoice .="<tr><td align=right colspan=5>$rs[0]&nbsp;</td><td>".number_format($rs[1],2)."</td><td>".number_format($rs[2],2)."</td></tr>";
				  }
				  //
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
	
	public function actionRmcno2($cno)
	{
	   unset($_SESSION['JobFees'][$cno]);
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
		    $q ="SELECT oi.id,oi.cno,g.name,oi.iid FROM orderitems oi INNER JOIN itemgroup g ON g.gid = oi.gid INNER JOIN orders o ";
	        $q .="ON o.orderno = oi.orderno WHERE o.cid = '$cid' AND oi.hasbl ='Y' AND oi.paid ='N' AND NOT oi.id IN('$RS')"; 
		  }
		  else
		  {
			 $q ="SELECT oi.id,oi.cno,g.name,oi.iid FROM orderitems oi INNER JOIN itemgroup g ON g.gid = oi.gid INNER JOIN orders o ";
	         $q .="ON o.orderno = oi.orderno WHERE o.cid = '$cid' AND oi.hasbl ='Y' AND oi.paid ='N'";  
		  }
		$rslt = $conn->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	        $cnt = 1;
			$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$rs[1]'")->queryScalar();
			if($ccn)
			{
				$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
			}
			$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$rs[1]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $r)
					  {
						  $it .= $r[0].': Items : '.$r[2].' : Descr : '.$r[3].' , ';
						
					  }
					$it = rtrim($it,' , ');
		  $data[$rs[0]] = $rs[1].' - '.$rs[2].' : '.$it;
	     }

		 return $data;	
	}
	
	
	public function actionNoaccess()
	{
		return $this->render('noaccess');
	}


}
