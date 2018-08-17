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
use app\models\Orderitems2;
use app\models\Orderitemsb;
use app\models\Squeeze;
use app\models\Squeeze2;
use app\models\Squeeze3;
use app\models\Squeeze4;
use app\models\SqueezeC;
use app\models\Manifest;
use app\models\Manifest2;
use app\models\RO;
use app\models\Shipping;
use app\models\FCLShipping;
use app\models\Uorder;
use app\models\Rorders;
use app\models\Supcontacts;
use app\models\Shipping2;
use app\models\Shipping3;
use app\models\Jobsrch;
use app\models\Sas;
use app\models\LogisticsRoles;
use yii\helpers\Html;
use yii\helpers\Json;
use kartik\mpdf\Pdf;


class OperationController extends Controller
{
  	public $menu = 'opmenu';
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
        
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
      
		return $this->render('index',['model'=>$model,'tbTrace'=>$tbTrace]);
    }
	
	
	
	public function actionMvstock()
	{
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		 
		  $model = new Stock;
		  $hasTotal = false;
		  
		  if(isset($_POST['btnGeneral'])) 
		  {
			$num = Yii::$app->db->createCommand("SELECT movno FROM accode")->queryScalar();
		    Yii::$app->db->createCommand("UPDATE accode SET movno = movno + 1")->execute();
		    $instr = str_shuffle($this->getCode().$num);

			 $id = Yii::$app->user->id;
		  if($_SESSION['JobItems'])
		   {	
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				      $ck = explode(":",$ckey);
				     
					    $oldloc = Yii::$app->db->createCommand("SELECT locid FROM orderitems WHERE id ='$ck[1]'")->queryScalar();
		                $qhc ="UPDATE orderitems SET locid = '$ck[2]', olocid = '$oldloc', movcode = '$instr', movby = '$id',";
                        $qhc .="movdate = CURDATE(), movtime = NOW() WHERE id = '$ck[1]'";
		                Yii::$app->db->createCommand($qhc)->execute();
				   
		        }
		
					  
		                unset($_SESSION['JobItems']);  //remove ALL Items to the session variable
						
			
			 return $this->redirect(['operation/pmovement','cid'=>$instr]);
		               
	          }
	
			 
							 
		  }
		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				//add this item to the session variable 
				$_SESSION['JobItems'][$model->orderno.':'.$model->prodid.':'.$model->locid]++; 
				
			   $model->locid = '';
			   $model->prodid = '';
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
		   $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>ORDERNO</th><th>Batch No</th>";
		   $tbInvoice .="<th>Code</th><th>Product</th><th>Current Location</th><th>New Location</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $rw = count($_SESSION['JobItems']);	 
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $qp ="SELECT o.orderno,oi.batchno,oi.barcode,p.name,l.name FROM orderitems oi INNER JOIN products p  ";
					  $qp .="ON p.prodid = oi.prodid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN ";
					  $qp .="locations l ON l.locid = oi.locid WHERE oi.id ='$ck[1]'";
					  $dt = Yii::$app->db->createCommand($qp)->queryOne(0);
					  $newloc = Yii::$app->db->createCommand("SELECT name FROM locations WHERE locid ='$ck[2]'")->queryScalar();
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem2','cid'=>$ckey])."</b></td>";
					 $tbInvoice .="<td>$dt[0]</td><td>$dt[1]</td><td>$dt[2]</td><td>$dt[3]</td><td>$dt[4]</td><td>$newloc</td></tr>";
					$i++; 
				  }
				 }
				 $tbInvoice .="</table>";
		return $this->render('_fsmove',['model'=>$model,'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
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

public function actionCargos()
    {
        
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		
          $q2 ="SELECT oi.scode 'Shipping Code',sl.name as 'Shipping Line',oi.containerno as 'Container No',";
		  $q2 .="DATE_FORMAT(oi.expsdate,'%d/%m/%Y') as 'Shipping Date',DATE_FORMAT(oi.expardate,'%d/%m/%Y') as 'Arrived Date', ";
		  $q2 .="COUNT(oi.iid) AS 'Loaded Items' FROM orderitems oi INNER JOIN sline as sl ON sl.slid = oi.slid WHERE oi.hasbl = 'Y' ";$q2 .="GROUP BY oi.scode,sl.name,oi.containerno,oi.expsdate,oi.expardate ORDER BY oi.expsdate DESC";
		
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
      
         	  
		return $this->render('slhist',['dataProvider'=>$dataProvider]);
    }


public function actionNsorders()
    {
        unset($_SESSION['JobItems']);
		unset($_SESSION['JobCharge']);
		unset($_SESSION['JobItems2']);
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		
          $q2 ="SELECT oi.orderno as 'Order No',oi.cno as 'Control No',oi.iid,oi.id,oi.pcalc,";
		  $q2 .="CASE oi.hascbm WHEN 'Y' THEN oi.cbm ELSE 'NA' END as 'CBM',";
		  $q2 .="CASE oi.pcalc WHEN 'NOW' THEN FORMAT(price,2) ELSE 'LATER' END as 'Price USD',c.name as 'Client', ";
		  $q2 .="DATE_FORMAT(oi.cdate,'%d/%m/%Y') as 'Received Date',DATEDIFF(NOW(),oi.cdate) as 'dueon' ";
		  $q2 .="FROM orderitems oi INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
		  $q2 .="WHERE oi.shipped = 'N' AND oi.isb = 'N' ORDER BY oi.cdate";
		
		$cn = Yii::$app->db->createCommand($q2)->queryAll();
		$cnt = count($cn);
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Order No','Control No','Client'],],
							'pagination'=>[
							               'pageSize'=>20,
	                                      ],
						    ]);	
      
         	  
		return $this->render('movhist',['dataProvider'=>$dataProvider]);
    }
	
	public function actionUpdatesq($oid)
	{
		     $Total = 0;
			 $id = Yii::$app->user->id;
	  if($_SESSION['JobItems2'])
		{	
          foreach($_SESSION['JobItems2'] as $ckey=>$val)
		        {
                 	$ck = explode(":",$ckey);
				     $pr = Yii::$app->db->createCommand("SELECT rate FROM itemgroup WHERE gid ='$ck[0]'")->queryScalar();
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
					  
		                $it = '';
						foreach($_SESSION['JobItems'] as $ckey2=>$val2)
						{
						  $k = Yii::$app->db->createCommand("SELECT iid FROM orderitems WHERE id = '$ckey2'")->queryScalar();
						  $it .= $k.',';
						}
				        $it = rtrim($it,',');
						
						$qhc ="INSERT INTO orderitems(gid,iid,orderno,cno,hascbm,cbm,pcalc,price,nop,descr,cby,cdate,repacked)";
                        $qhc .=" VALUES('$ck[0]','$it','$oid','$ck[3]','$hascbm','$cbm','NOW','$price','$ck[4]','$ck[5]','$id',NOW(),'Y')";
		                Yii::$app->db->createCommand($qhc)->execute();				
                }
				
		Yii::$app->db->createCommand("UPDATE orders SET squeezed = 'Y',sqby = '$id',sqdate =NOW() WHERE orderno ='$oid'")->execute();	
                  				
		  if($_SESSION['JobItems'])
		   {	
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				     
		                $qhc ="INSERT INTO osqueezed(gid,orderno,ncno,cno,iid,hascbm,cbm,pcalc,price,cby,cdate) SELECT ";
                        $qhc .="gid,orderno,'$ck[3]',cno,iid,hascbm,cbm,pcalc,price,cby,cdate FROM orderitems WHERE id ='$ckey'";
		                Yii::$app->db->createCommand($qhc)->execute(); 
						Yii::$app->db->createCommand("DELETE FROM orderitems WHERE id = '$ckey'")->execute(); 
		        }

		             
					 
					  
		                
					 
		  }
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['JobItems2']);  //remove ALL chassis number to the session variable
						
			
			 Yii::$app->session->setFlash('osuccess','Customer Items has been successful Squeezed/Repacked');
			 $this->redirect(['operation/showordersq','oid'=>$oid,'ncno'=>$ck[3]]);
		}
	}
	
	public function actionSqueeze($oid)
	{
		
		$cid = Yii::$app->db->createCommand("SELECT cid FROM orders WHERE orderno =:oid")->bindParam(':oid',$oid)->queryScalar();
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }

		  $model = new Squeeze;
		  $model2 = new SqueezeC;
		  $hasTotal = false;
		  $hasItems2 = false;
		  
		  if(isset($_POST['btnAdd2'])) 
		  {  
		  if($model2->load(Yii::$app->request->post()) && $model2->validate())
		   {
		   
				//add this item to the session variable
                unset($_SESSION['JobItems2']);		
                		
                		$num = Yii::$app->db->createCommand("SELECT cno FROM accode")->queryScalar();
				Yii::$app->db->createCommand("UPDATE accode SET cno = cno + 1")->execute();
				$model2->cno = str_shuffle($this->getCno().$num);
                		
                $_SESSION['JobItems2'][$model2->iid.':'.$model2->cbm.':'.$model2->cno.':'.$model2->nop.':'.$model2->descr]++;    
			    $this->refresh(); 
				
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
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Control No</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>CBM</th><th>No Of Items</th><th>Package Description</th><th>Calculation</th><th>Price (USD)</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  $Total = 0;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $qi = "SELECT g.name,g.rate,oi.iid,oi.hascbm,oi.cbm,oi.pcalc,oi.price,oi.cno,oi.nop,oi.descr FROM orderitems oi INNER JOIN ";
                      $qi .="itemgroup g ON g.gid = oi.gid WHERE oi.id = '$ckey'";					  
					  $ck = Yii::$app->db->createCommand($qi)->queryOne(0);
					  
					  if($ck[5] == 'NOW')
					  {
					  if($ck[3] == 'Y')
					  {
						$price = $ck[6];  
						$cbm = $ck[4];
						$calc = $ck[1]. ' X '. $ck[4];
					  }
					  else
					  {
						 $price = $ck[6];  
						 $calc = '1 X '. $ck[6];
						 $cbm = 'NA'; 
					  }
					  
					  $Total = $Total + $price;
					  $price2 = number_format($price,2);
					  }
					  else
					  {
						$cbm = 'NA'; 
						$price2 = 'Later'; 
						$calc = '';
                        if($ck[3] == 'Y')
					    { 
						$cbm = $ck[4];
					   }						
					  }
					  
					  $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[2]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $it = rtrim($it,' , ');
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitemsq','cid'=>$ckey,'oid'=>$oid])."</b></td><td>$ck[7]</td>";
		             $tbInvoice .="<td>$ck[0]</td><td>$it</td><td>$cbm</td><td>$ck[8]</td><td>$ck[9]</td><td>$calc<td>$price2</td><tr>";
				
					 $i++; 
				  }
			 $tbInvoice .="<tr><td colspan=9 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 
				 if($_SESSION['JobItems2'])
				 {
				  $hasItems2 = true;
				  $tt2 = 0;
				  foreach($_SESSION['JobItems2'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $pr = Yii::$app->db->createCommand("SELECT name,rate FROM itemgroup WHERE gid ='$ck[0]'")->queryOne(0);
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
					  
					  $tt2 = $tt2 + $price;
					  
					//Combile All the Items Into One Group of Items That Has Been Squeezed Or Repacked
					$it = '';
					foreach($_SESSION['JobItems'] as $ckey2=>$val2)
		            {
					  $k = Yii::$app->db->createCommand("SELECT iid FROM orderitems WHERE id = '$ckey2'")->queryScalar();
					  $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($k) ORDER BY name")->queryAll(false);
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					}
				$it = rtrim($it,' , ');
			$tbInvoice .="<tr bgcolor='#D3D3D3'><th colspan=10><b>After Squeezing Details</b></th></tr>";		 
			$tbInvoice .="<tr><td>1.</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem3','cid'=>$ckey,'oid'=>$oid])."</b></td>";
		             $tbInvoice .="<td><b>New Control No:</b>&nbsp;$ck[3]</td><td>$pr[0]</td><td>$it</td><td>$cbm</td><td>$ck[4]</td><td>$ck[5]</td><td>$calc<td><b>".number_format($tt2,2)."</b></td><tr>";
					 $tbInvoice .="<tr><td colspan=10 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Fine.Save Squeezed Items',['operation/updatesq','oid'=>$oid],['data'=>['confirm'=>'Submit And Save Repacked Items?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				
					 
				  }
				 }
				 
				 $tbInvoice .="</table>";
		         return $this->render('_fsitems',['model'=>$model,'model2'=>$model2,'tbDet'=>$this->getCDet($cid,$oid,'Y'),
		                               'tbInv'=>$tbInvoice,'hasItems'=>$hasItems,'cid'=>$cid]);
		  

		  
	}
        public function getSQIB($cid)
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
	        $q .="WHERE o.cid = '$cid' AND oi.shipped ='N' AND oi.isb = 'Y' AND oi.bstage = 'R' AND NOT oi.id IN('$RS') ORDER BY oi.cno"; 
		  }
		  else
		  {
			//$q ="SELECT oi.cno,CONCAT(oi.cno,' - ',i.name) FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid ";
	        //$q .="WHERE oi.orderno = '$oid' ORDER BY oi.cno"; 
            $q ="SELECT oi.id,oi.cno,oi.iid FROM orderitems oi INNER JOIN orders o ON o.orderno = oi.orderno ";
	        $q .="WHERE o.cid = '$cid' AND oi.shipped = 'N' AND oi.isb = 'Y' AND oi.bstage = 'R' ORDER BY oi.cno";			
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
		    $q ="SELECT oi.id,g.cper,oi.cno,oi.iid,g.name FROM orderitems oi INNER JOIN itemgroup g ON g.gid = oi.gid ";
	        $q .="WHERE oi.id = '$id' AND oi.shipped ='N' AND NOT oi.id IN('$RS') ORDER BY oi.cno"; 
		  }
		  else
		  {
			//$q ="SELECT oi.cno,CONCAT(oi.cno,' - ',i.name) FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid ";
	        //$q .="WHERE oi.orderno = '$oid' ORDER BY oi.cno"; 
            $q ="SELECT oi.id,g.cper,oi.cno,oi.iid,g.name FROM orderitems oi INNER JOIN itemgroup g ON g.gid = oi.gid ";
	        $q .="WHERE oi.id = '$id' AND oi.shipped = 'N' ORDER BY oi.cno";			
		  }
		$rslt = $conn->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
	       /*$items = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($rs[3]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($items as $item)
					  {
						  $it .= $item[0].' , ';
						
					  }
					  $it = rtrim($it,' , ');*/
		   $data[$rs[0].':'.$rs[1]] = $rs[2].' - '.$rs[4];
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
	
	public function actionPlist($scode)
	{
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 1";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
       
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		
		
		$qs = "SELECT sl.name,oi.containerno,oi.cntsize,DATE_FORMAT(oi.expsdate,'%d/%m/%Y'),DATE_FORMAT(oi.expardate,'%d/%m/%Y') ";
		$qs .="FROM orderitems oi INNER JOIN sline sl ON sl.slid = oi.slid ";
		$qs .="WHERE oi.scode =:scode LIMIT 1";
		
		$dt = Yii::$app->db->createCommand($qs)->bindParam(':scode',$scode)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table cellpadding=1 border=1 cellspacing=0 width=100%>";
			 $tbH .="<tr bgcolor='#CACCCE'><th colspan=4>SHIPPING DETAILS</th></tr>";
			 $tbH .="<tr><th width=20%>Shipping Line</th><td>$dt[0]</td><th>Shipping Code</th><td><i>$scode</i></td></tr>";
		     $tbH .="<tr><th>Container No</th><td>$dt[1]</td><th>Container Size</th><td>$dt[2]</td></tr>";
			 $tbH .="<tr><th>Expected Shipping Date</th><td>$dt[3]</td>";
			 $tbH .="<th>Expected Arrival Date</th><td>$dt[4]</td></tr>";
			 $tbH .="</table><br />";
		
		  $qo = "SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,c.name,o.sas,g.name,oi.isb FROM orderitems oi INNER JOIN itemgroup g ";
		  $qo .="ON g.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
		  $qo .="WHERE oi.scode =:scode ";
		  $orders = Yii::$app->db->createCommand($qo)->bindParam(':scode',$scode)->queryAll(false);
	
		if(!empty($orders))
		{ 
	        $i = 1;
			$Total = 0;
			
			$tbO .="<table cellpadding=1 border=1 cellspacing=0 width=100%><tr><td colspan=8><b>ITEMS LOADED</b></td></tr>";
		    $tbO .="<tr bgcolor='#CACCCE'><th>SN</th><th>Control Number</th><th>Client</th><th>Shipping As</th><th>Cargo Type</th>";
		    $tbO .="<th>Items</th><th>Total CBM</th></tr></thead>";
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
					  $cargo = 'Loose Cargo';
					  if($order[8] == 'Y')
					  {
						 $cargo = 'Bale'; 
					  }
					  
				$cnt = 1;
			$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$order[0]'")->queryScalar();
			if($ccn)
			{
				$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
			}
			$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$order[0]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= '<b>'.$rs[0].'</b>: Items : '.$rs[2].' : Descr : '.$rs[3].' , ';
						
					  }
					$it = rtrim($it,' , ');
					  
					  $Total = $Total + $order[4];
	                  $tbO .="<tr><td>$i</td><td>$order[0]</td><td>$order[5]</td><td>$order[6]</td><td>$cargo</td><td>$it</td><td>$cbm</td></tr>";
				      //$tbO .="<td>".number_format($order[4],2)."</td><td>$order[5]</td><td>$order[6]</td></tr>";
				      $i++;
			  }
			  $tbO .="</table>";
			   //$tbO .=" <tfoot><tr><td colspan=5 align=right><b><i>Total:</i></b></td>";
			   //$tbO .="<th colspan=3>".number_format($Total,2)."</th></tr></tfoot></table>";
		  }
		 }  
		//return $tbH.$tbO;
		
			date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
         
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE,  
        				'destination' => Pdf::DEST_BROWSER, 
                         'content' =>$logo.$tbH.$tbO,  						 
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Silent Ocean Logistics Management System'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
		
	}
	
	public function getCntItems($scode)
	{
		
	    $qs = "SELECT sl.name,oi.containerno,oi.cntsize,DATE_FORMAT(oi.expsdate,'%d/%m/%Y'),DATE_FORMAT(oi.expardate,'%d/%m/%Y') ";
		$qs .="FROM orderitems oi INNER JOIN sline sl ON sl.slid = oi.slid ";
		$qs .="WHERE oi.scode =:scode LIMIT 1";
		
		$dt = Yii::$app->db->createCommand($qs)->bindParam(':scode',$scode)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table class='table table-bordered table-gray'>";
			 $tbH .="<thead><tr><th colspan=4>SHIPPING DETAILS</th></tr></thead>";
			 $tbH .="<tr><th width=20%>Shipping Line</th><td>$dt[0]</td><th>Shipping Code</th><td><i>$scode</i></td></tr>";
		     $tbH .="<tr><th>Container No</th><td>$dt[1]</td><th>Container Size</th><td>$dt[2]</td></tr>";
			 $tbH .="<tr><th>Expected Shipping Date</th><td>$dt[3]</td>";
			 $tbH .="<th>Expected Arrival Date</th><td>$dt[4]</td></tr>";
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
					  
				$cnt = 1;
			$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$order[0]'")->queryScalar();
			if($ccn)
			{
				$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
			}
			$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$order[0]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= '<b>'.$rs[0].'</b>: Items : '.$rs[2].' : Descr : '.$rs[3].' , ';
						
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
	
	
	public function actionManifest()
	{
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }

		  $model2 = new Manifest;
		  $models = $this->getBLCnt();
		  $hasTotal = false;
		  $hasItems2 = false;
		  
		  if(isset($_POST['btnAdd2'])) 
		  {  
		  if($model2->load(Yii::$app->request->post()) && $model2->validate())
		   {
				//add this item to the session variable
                unset($_SESSION['JobItems2']);				
                $_SESSION['JobItems2'][$model2->bltype.':'.$model2->blno]++;   
			    $this->refresh(); 
				
		   }
          }
		 if(isset($_POST['btnAdd'])) 
		  {  
			    foreach($models as $i=>$mod)
			    {
					
			      $mod[6]->attributes = $_POST['Shipping3'][$i];
				  if($mod[6]->picked == 1)
				   {
					  $_SESSION['CntCargos'][$mod[6]->scode]++; 
				   }
			    }
			
			  $models = $this->getBLCnt();
          }
		  
		   $i = 1;
		   $Total = $number = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Shipping Line</th>";
		   $tbInvoice .="<th>Container #</th><th>Total Cargos</th><th>Total Amount</th><th>Exp. Arrival Date</th></tr>";
		   
		   if($_SESSION['CntCargos'])
				 {
				  $hasItems = true;
				  $Total = 0;
				  $i = 1;
				  foreach($_SESSION['CntCargos'] as $ckey=>$val)
		          {
					 $qi = "SELECT sl.name,oi.containerno,oi.scode,COUNT(oi.scode),SUM(oi.price),DATE_FORMAT(oi.expardate,'%d/%m/%Y') ";
					  $qi .="FROM orderitems oi INNER JOIN sline sl ON sl.slid = oi.slid WHERE oi.scode = '$ckey' ";
                      $qi .="GROUP BY sl.name,oi.containerno,oi.scode";			  
					  $ck = Yii::$app->db->createCommand($qi)->queryOne(0);
					  
					 $Total = $Total + $ck[4];
					 $number = $number + $ck[3];
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmcntbl','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$ck[0]</td><td>$ck[1]</td><td>".number_format($ck[3])."</td>";
					 $tbInvoice .="<td><b>".number_format($ck[4])."</b></td><td>$ck[5]</td><tr>";
				
					 $i++; 
				  }
			  $tbInvoice .="<tr><td colspan=4 align=right><b>Total</b></td><td><b>".number_format($number)."</b></td>";
			  $tbInvoice .="<td colspan=2><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 
				 if($_SESSION['JobItems2'])
				 {
				  $hasItems2 = true;
				  $tt2 = 0;
				  foreach($_SESSION['JobItems2'] as $ckey=>$val)
		          {
					$ck = explode(":",$ckey);
					  	  
			$tbInvoice .="<tr bgcolor='#D3D3D3'><th colspan=7><b>BL Details</b></th></tr>";		 
			$tbInvoice .="<tr><td>1.</td><td><b>".Html::a("<b>Remove</b>",['operation/rmbl','cid'=>$ckey])."</b></td>";
		    $tbInvoice .="<td align='right'>BL Type:&nbsp;<b>$ck[0]</b></td><td colspan=2>&nbsp; BL #:&nbsp;<b>$ck[1]</b></td>";
					 $tbInvoice .="<td colspan=2 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Save Container BL',['operation/savebl'],['data'=>['confirm'=>'Save Container BL?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				
					 
				  }
				 }
				 
				 $tbInvoice .="</table>";
		         return $this->render('_fcntbl',['models'=>$models,'model2'=>$model2,
		                               'tbInv'=>$tbInvoice,'hasItems'=>$hasItems,'cid'=>$cid]);
		  

		  
	}
	
	public function actionEloading()
	{
		
	if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $cid = $_SESSION['cid'];
		  $models = $this->getUNLItems2();
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
			
			  $models = $this->getUNLItems2();
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
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem7','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$pr[0]</td><td>$pr[7]</td><td>$it</td><td>$cbm</td><td>".number_format($pr[4],2)."</td>";
					 $tbInvoice .="<td>$pr[5]</td><td>$pr[6]</td></tr>";
				
					 $i++; 
				  }
			 $tbInvoice .="<tr><td colspan=9 align=right><b>".Html::a('<i class="glyphicon glyphicon-list-alt"></i> Update Selected Items Into Container',['operation/cntloading2'],['data'=>['confirm'=>'Update Selected Items to this Container?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				 }
				 $tbInvoice .="</table>";
		return $this->render('_fclitems2',['tbDet'=>$this->getContDet(),'models'=>$models,
		'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  	  
	}
	
	public function actionCntloading2()
	{
	  
	  if($_SESSION['JobItems'])
		{	
          $id = Yii::$app->user->id;
		  $scode = $_SESSION['scode'];
		  $slid = $_SESSION['slid'];
		  $cntno = $_SESSION['containerno'];
		  $sdates = explode("/",$_SESSION['expsdate']);
		  $tdates = explode("/",$_SESSION['expardate']);
		  
		  $sdate = $sdates[2].'-'.$sdates[1].'-'.$sdates[0];
		  $tdate = $tdates[2].'-'.$tdates[1].'-'.$tdates[0];
		  
		 $q ="UPDATE orderitems SET shipped = 'N', scode = NULL,containerno = NULL, slid = NULL WHERE scode = '$scode' AND hasbl = 'N'";
		 Yii::$app->db->createCommand($q)->execute();
		   
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
						unset($_SESSION['scode']);
						
			
			 Yii::$app->session->setFlash('osuccess','Container Cargos Has Been Successfull Updated');
			 $this->redirect(['operation/showitc','scode'=>$scode]);
		}
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
			unset($_SESSION['cntsize']);
			unset($_SESSION['expsdate']);
			unset($_SESSION['expardate']);
		$model = new Shipping;
		 if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		 {
			 $_SESSION['slid'] = $model->slid;
			 $_SESSION['containerno'] = $model->containerno;
			 $_SESSION['cntsize'] = $model->cntsize;
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
		  $cntsize = $_SESSION['cntsize'];
		  $sdates = explode("/",$_SESSION['expsdate']);
		  $tdates = explode("/",$_SESSION['expardate']);
		  
		  $sdate = $sdates[2].'-'.$sdates[1].'-'.$sdates[0];
		  $tdate = $tdates[2].'-'.$tdates[1].'-'.$tdates[0];
		  
		  $num = Yii::$app->db->createCommand("SELECT shipcode FROM accode")->queryScalar();
		    Yii::$app->db->createCommand("UPDATE accode SET shipcode = shipcode + 1")->execute();
		    $scode = str_shuffle($this->getCode().$num);
		  foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
                    $q ="UPDATE orderitems SET slid ='$slid',scode='$scode',shipped ='Y',containerno ='$cntno',cntsize='$cntsize',";
					$q .="expsdate='$sdate',expardate='$tdate',picked = 'Y',sby='$id',sdate=NOW() WHERE id='$ckey'";
				    Yii::$app->db->createCommand($q)->execute();
					  		
                }
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['slid']);  //remove ALL chassis number to the session variable
						unset($_SESSION['containerno']);
						unset($_SESSION['cntsize']);
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
		  $mod = new Jobsrch;
		  $models = $this->getUNLItems();
		  $hasTotal = false;
		  
		 if(isset($_POST['btnSearch'])) 
		{
			if($mod->load(Yii::$app->request->post()) && $mod->validate())
			{
				//$cno = explode(':',$mod->jid);
				$cno = $mod->jid;
				$query ="SELECT oi.cno,g.name,oi.iid,oi.hascbm,oi.cbm,price,c.name,o.sas,oi.id FROM orderitems oi INNER JOIN orders o ON ";
				$query .="oi.orderno = o.orderno INNER JOIN clients c ON o.cid = c.cid INNER JOIN itemgroup g ON oi.gid = g.gid ";
				$query .=" WHERE oi.cno = '$cno'";
				$rs = Yii::$app->db->createCommand($query)->queryOne(0);
				if($rs) 
				{
					$tbJob = "<table class='table table-bordered table-gray'><thead><tr><th>CONTROL NO</th><th>ITEM GROUP</th><th>ITEMS</th>";
					$tbJob .= "<th>CBM</th><th>PRICE (USD)</th><th>CLIENT</th><th>SHIPPING AS</th><th>ACTION</th></tr></thead>";

					$tbJob .="<tr><td>$rs[0]</td><td>$rs[1]</td>";
					
					$cnt = 1;
					$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$rs[0]'")->queryScalar();
					if($ccn)
					{
						$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
					}
					$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$rs[0]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $r)
					  {
						  $it .= '<b>'.$r[0].'</b>: Items : '.$r[2].' : Descr : '.$r[3].' , ';
						
					  }
					$it = rtrim($it,' , ');
					
					$tbJob .="<td>$it</td>";
					
					if($rs[3] == 'Y')
					{
						$tbJob .= "<td>$rs[4]</td>";
					}
					else
					{
						$tbJob .= "<td>NA</td>";
					}
					
					$tbJob .="<td>$rs[5]</td><td>$rs[6]</td><td>$rs[7]</td><td>".Html::a('Add',['operation/tosession','id'=>$rs[8]],['class'=>'btn green btn-xs'])."</td></tr>";
					
					$tbJob .="</table>";
				} 
				else 
				{
					Yii::$app->session->setFlash('error', 'Cargo Details You Submitted Does not Exist.');
				}
			}
		}
		 
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
					  
					 $cnt = 1;
					$ccn = Yii::$app->db->createCommand("SELECT ccno FROM orderitems WHERE cno ='$rs[0]'")->queryScalar();
					if($ccn)
					{
						$cnt = Yii::$app->db->createCommand("SELECT COUNT(ccno) FROM orderitems WHERE ccno ='$ccn'")->queryScalar();
					}
					$rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$rs[0]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $r)
					  {
						  $it .= '<b>'.$r[0].'</b>: Items : '.$r[2].' : Descr : '.$r[3].' , ';
						
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
		return $this->render('_fclitems',['tbDet'=>$this->getContDet(),'models'=>$models,'mod'=>$mod,
		'tbInv'=>$tbInvoice,'hasItems'=>$hasItems,'tbJob'=>$tbJob]);
		  	  
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
	
	public function getBLCnt()
	{
		 if($_SESSION['CntCargos'])
		  {
		    foreach($_SESSION['CntCargos'] as $ckey=>$val) 
			{
			  $ck = explode(":",$ckey);
			  $Aselected .=$ck[0].",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
			 
			 $q ="SELECT sl.name,oi.containerno,oi.scode,DATE_FORMAT(oi.expardate,'%d/%m/%Y'),COUNT(oi.scode),SUM(oi.price) "; 
		     $q .="FROM orderitems oi INNER JOIN sline AS sl ON sl.slid = oi.slid WHERE oi.shipped = 'Y' AND oi.hasbl = 'N' ";
		     $q .="AND NOT oi.scode IN('$RS') GROUP BY oi.scode,sl.name,oi.containerno ";
			
		    
		  }
		  else
		  {
			 $q ="SELECT sl.name,oi.containerno,oi.scode,DATE_FORMAT(oi.expardate,'%d/%m/%Y'),COUNT(oi.scode),SUM(oi.price) "; 
		     $q .="FROM orderitems oi INNER JOIN sline AS sl ON sl.slid = oi.slid WHERE oi.shipped = 'Y' AND oi.hasbl = 'N' ";
		     $q .="GROUP BY oi.scode,sl.name,oi.containerno ";
		  }	
		 
		$data = [];
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		foreach($rst as $rs)
		{
			
			
			$bsc = new Shipping3;
			$bsc->scode = $rs[2];
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
	///////////////////////////////////////////////////////////
	////BELLOW ORDER
	
	public function getBellow()
	{
	   $data = [];
	   
		  
		  $conn = Yii::$app->db;
		  if($_SESSION['JobB'])
		  {
		    foreach($_SESSION['JobB'] as $ckey=>$val) 
			{
			 $ck = explode(':',$ckey);
			 $Aselected .=$ck[2].",";
			}
			 $NS = str_replace(",","','",$Aselected);
	         $RS = substr_replace($NS,"",-3);
		    $q ="SELECT gid,name FROM itemgroup WHERE cper = 'BELLOW' AND NOT gid IN('$RS')";
	       // $q .="WHERE oi.id = '$id' AND oi.shipped ='N' AND NOT oi.id IN('$RS') ORDER BY oi.cno"; 
		  }
		  else
		  {
            $q ="SELECT gid,name FROM itemgroup WHERE cper = 'BELLOW' ";		
		  }
		$rslt = $conn->createCommand($q)->queryAll(false);
	    foreach($rslt as $rs)
	     {
		   $data[$rs[0]] = $rs[1];
	     }

		 return $data;	
	}

	
	public function actionBprocessing($ono,$id)
	{
		$rs = Yii::$app->db->createCommand("SELECT orderno,cid,sas,orderdate FROM orders WHERE orderno =:ono")->bindParam(':ono',$ono)->queryOne(0);
		$_SESSION['orderno'] = $rs[0];
		$_SESSION['cid'] = $rs[1];
		$_SESSION['sas'] = $rs[2];
		$_SESSION['odate'] = $rs[3];
		$cid = $rs[1];
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }

		  $model = new Squeeze4;
		  $hasTotal = false;
		  $hasItems2 = false;

		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				//add this item to the session variable 
				 $num = Yii::$app->db->createCommand("SELECT bcno FROM accode")->queryScalar();
		         Yii::$app->db->createCommand("UPDATE accode SET bcno = bcno + 1")->execute();
		         $cno = str_shuffle($this->getCode().$num);
                $_SESSION['JobB'][$model->iid.':'.$model->bno.':'.$model->quantity.':'.$model->cbm.':'.$cno]++; 			
			    $this->refresh(); 
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'>";
		   $tbInvoice .= "<tr><th>SN</th><th>Option</th><th>Control No</th><th>Bellow Type</th>";
		   $tbInvoice .="<th># Of Bellows</th><th>CBM</th><th>Calculation</th><th>Price (USD)</th></tr>";
		   
		   if($_SESSION['JobB'])
				 {
				  $hasItems = true;
				  $Total = 0;
				  foreach($_SESSION['JobB'] as $ckey=>$val)
		          {
					  $ml = explode(':',$ckey);
					  $qi = "SELECT g.name,g.rate FROM itemgroup g WHERE g.gid = '$ml[2]'";				  
					  $ck = Yii::$app->db->createCommand($qi)->queryOne(0);
					  
					  if($ml[2] == 8)
					  {
						$price = $ml[3] * $ck[1];  
						$cbm = 'NA';
						$calc = $ml[3].' X '. $ck[1];
						$quantity = $ml[3];
					  }
					  else
					  {
						 $price = $ml[4] * $ck[1];  
						 $calc = $ml[4] .' X '. $ck[1];
						 $cbm = $ml[4]; 
						 $quantity = 1; 
					  }
					  
					 
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmbellow','cid'=>$ckey,'ono'=>$ono,'id'=>$id])."</b></td><td>$ml[5]</td>";
		             $tbInvoice .="<td>$ck[0]</td><td>$quantity</td><td>$cbm</td><td>$calc<td><b>".number_format($price,2)."</b></td><tr>";
				     $Total = $Total + $price; 
					 $i++; 
				  }
				  
                 $d = Yii::$app->db->createCommand("SELECT iid,cno FROM orderitems WHERE id ='$ml[0]'")->queryOne(false);
                 $rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,p.nopieces,p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid WHERE p.cno ='$d[1]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= '<b>'.$rs[0].'</b>: Package : '.$rs[1].' : Items : '.$rs[2].' , ';
						
					  }
					  $it = rtrim($it,' , ');	
				  
			// $tbInvoice .="<tr><td colspan=7 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 $tbInvoice .="<tr><tr><td colspan=6>Master Cargo Control Number : <b>$d[1]</b>, Bale Items : $it</td><td align=right><i>Total:</i></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 $tbInvoice .="<tr><td colspan=8 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Done.Save Bellows ',['operation/updatebcost','id'=>$id],['data'=>['confirm'=>'Submit And Save?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				 
				 $tbInvoice .="</table>";
		         return $this->render('_fbuitems',['model'=>$model,'tbDet'=>$this->getCDet($cid),
		                               'tbInv'=>$tbInvoice,'hasItems'=>$hasItems,'id'=>$id]);
		  
		
	}
	
	public function actionUpdatebcost($id)
	{
		  $cd = Yii::$app->db->createCommand("SELECT iid,orderno,cno FROM orderitems WHERE id = '$id'")->queryOne(false);
		   if($_SESSION['JobB'])
				 {
				  $hasItems = true;
				  $Total = 0;
				  foreach($_SESSION['JobB'] as $ckey=>$val)
		          {
					  $ml = explode(':',$ckey);
					  $qi = "SELECT g.name,g.rate FROM itemgroup g WHERE g.gid = '$ml[2]'";				  
					  $ck = Yii::$app->db->createCommand($qi)->queryOne(0);
					  
					  if($ml[2] == 8)
					  {
						$quantity = $ml[3];
						for($i = 0; $i < $quantity; $i++)
						{
						 $qhc ="INSERT INTO orderitems(gid,iid,orderno,ccno,cno,isb,bstage,hascbm,cbm,pcalc,price,cby,cdate)";
                         $qhc .=" VALUES('$ml[2]','$cd[0]','$cd[1]','$cd[2]','$ml[5]','Y','P','N',0,'NOW','$ck[1]','$id',NOW())";
						 Yii::$app->db->createCommand($qhc)->execute();
						 
						}
						Yii::$app->db->createCommand("INSERT INTO plist(iid,nop,nopieces,descr,cno) SELECT iid,nop,nopieces,descr,'$ml[5]' FROM plist WHERE cno ='$cd[2]'")->execute();
					  }
					  else
					  {
						 $price = $ml[4] * $ck[1];  
					     $qhc ="INSERT INTO orderitems(gid,iid,orderno,ccno,cno,isb,bstage,hascbm,cbm,pcalc,price,cby,cdate)";
                   $qhc .=" VALUES('$ml[2]','$cd[0]','$cd[1]','$cd[2]','$ml[5]','Y','P','Y','$ml[4]','NOW','$price','$id',NOW())";
						 Yii::$app->db->createCommand($qhc)->execute();
						 Yii::$app->db->createCommand("INSERT INTO plist(iid,nop,nopieces,descr,cno) SELECT iid,nop,nopieces,descr,'$ml[5]' FROM plist WHERE cno ='$cd[2]'")->execute();
			
					  }
					  
					 
		              
					 
				  }
				  
                       $qhc ="INSERT INTO osqueezed(gid,orderno,isb,ncno,cno,iid,hascbm,cbm,pcalc,price,cby,cdate,expanded) SELECT ";
                       $qhc .="gid,orderno,'Y',cno,cno,iid,hascbm,cbm,pcalc,price,cby,cdate,'Y' FROM orderitems WHERE id ='$id'";
		               Yii::$app->db->createCommand($qhc)->execute();
					   
					   Yii::$app->db->createCommand("DELETE FROM orderitems WHERE id = '$id'")->execute();
				 }
				  unset($_SESSION['JobB']);  //remove ALL chassis number to the session variable

			 Yii::$app->session->setFlash('osuccess','Bale has been successful Processed');
			 $this->redirect(['operation/showpbellow','ccno'=>$cd[2]]);
				 
		  
		
	}
	
	public function actionShowpbellow($ccno)
	{
		$oid = Yii::$app->db->createCommand("SELECT orderno FROM orderitems where ccno =:ccno LIMIT 1")->bindParam(':ccno',$ccno)->queryScalar();
		$qs = "SELECT c.name,c.address,c.paddress,CONCAT('+',c.pcode,c.phone),c.pcode2,c.phone2,c.email,c.ctype,";
		$qs .="DATE_FORMAT(o.orderdate,'%d/%m/%Y') FROM orders o INNER JOIN clients c ON c.cid = o.cid ";
		$qs .="WHERE o.orderno =:oid";
		
		$dt = Yii::$app->db->createCommand($qs)->bindParam(':oid',$oid)->queryOne(0);
		
		 if(!empty($dt))
		  {
			 $tbH ="<table class='table table-bordered table-gray'><tr><td colspan=2><b>Order # : </b>$oid &nbsp;&nbsp;&nbsp;";
			 $tbH .="<b>Order DATE : </b>$dt[8]</td></tr>";
			 $tbH .="<thead><tr><th colspan=2>CLIENT DETAILS</th></tr></thead>";
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
		
		  $qo = "SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,g.rate,g.name,oi.pcalc FROM orderitems oi INNER JOIN itemgroup g ON ";
		  $qo .="g.gid = oi.gid WHERE oi.ccno =:ncno ";
		  
		  $orders = Yii::$app->db->createCommand($qo)->bindParam(':ncno',$ccno)->queryAll(false);
		  $cnt = count($orders);
	
		if(!empty($orders))
		{ 
	        $i = 1;
			$Total = 0;
			
			$tbO .="<table class='table table-bordered table-gray footable'><thead><tr><th colspan=7><b>BELLOWS DESCRIPTIONS</b></th></tr>";
			//$tbO .="<td colspan=4>&nbsp;</td><th><b>".Html::a('Edit',['operation/eorder','oid'=>$oid])."</b></th></tr>";
		    $tbO .="<tr><th>SN</th><th>Control Number</th><th>Item Group</th><th>Items</th><th>CBM</th><th>Calculation</th>";
		    $tbO .="<th>Price (USD)</th></tr></thead>";
		     foreach($orders as $order)
			  {
				if($order[7] == 'NOW')
                {					
			     if($order[2] == 'Y')
					  {
						$price = number_format($order[3] * $order[5],2);
                        $pr = number_format($order[3] * $order[5],2);  						
						$cbm = $order[3];
						$calc = $order[3]. ' X '. $order[5];
					  }
					  else
					  {
						 $price = number_format($order[5],2);  
						 $calc = '1 X '. $order[5];
						 $cbm = 'NA'; 
					  }
				}
				else
				{
					$price = 'Later';  
				    $calc = 'Later';
					 $cbm = 'NA'; 
				}
					  
				 $rst = Yii::$app->db->createCommand("SELECT i.name,p.nop,CEIL(p.nopieces/$cnt),p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid  WHERE p.cno = '$order[0]'")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= '<b>'.$rs[0].'</b>: Items : '.$rs[2].' : Descr : '.$rs[3].' , ';
						
					  }
					$it = rtrim($it,' , ');
					
					  $Total = $Total + $order[4];
				$tbO .="<tr><td>$i</td><td>$order[0]</td><td>$order[6]</td><td>$it</td><td><b>$cbm</b></td><td>$calc</td>";
				$tbO .="<td><b>$price</b></td></tr>";
				$i++;
			  }
			   $tbO .=" <tfoot><tr><td colspan=6 align=right><b><i>Total:</i></b></td><th>".number_format($Total,2)."</th></tr>";
			   $tbO .="</tfoot></table>";
		  }
		 }  
		
		return $this->render('vwo',['tbData'=>$tbH.$tbO]);
	}
	
	
	
	public function actionNewborder()
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
			 return $this->redirect(['operation/borders']);
		 }
		 
		 return $this->render('_fborder',['model'=>$model]);
		
	}
	public function actionGetbsas()
	{

         if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $pid = $parents[0];
			   
			    $q = "SELECT sas as id,sas as name FROM sas WHERE cid = '$pid' ORDER BY sas";
           	   $data = Yii::$app->db->createCommand($q)->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
	}
	
	public function actionBorders()
	{
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $cid = $_SESSION['cid'];
		  $model = new Orderitemsb;
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
					 if($ck[7] == 'NOW')
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
					  
		                $qhc ="INSERT INTO orderitems(gid,iid,orderno,isb,cno,hascbm,cbm,pcalc,price,cby,cdate)";
                        $qhc .=" VALUES('$ck[0]','$ck[2]','$orderno','Y','$ck[3]','$hascbm','$cbm','$ck[7]','$price','$id',NOW())";
		                Yii::$app->db->createCommand($qhc)->execute();
						
						$descr = str_replace("'","''",$ck[6]);
		                $qhc2 ="INSERT INTO plist(iid,nop,nopieces,descr,cno) VALUES('$ck[2]','$ck[4]','$ck[5]','$descr','$ck[3]')";
		                Yii::$app->db->createCommand($qhc2)->execute();
						
					  $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[4]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
					  }
					  $dt .= rtrim($it,' , ');
						$msg = "Received Package With Items [$dt] Has Been Given Control #: <b>$ck[3]</b>";
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
				 $num = Yii::$app->db->createCommand("SELECT bcno FROM accode")->queryScalar();
		         Yii::$app->db->createCommand("UPDATE accode SET bcno = bcno + 1")->execute();
		         $cno = 'B-'.str_shuffle($this->getCode().$num);
				$model->pcalc = 'LATER';
                $_SESSION['JobItems'][$model->gid.':'.$model->iid.':'.$cno.':'.$model->nop.':'.$model->nopieces.':'.$model->descr.':'.$model->pcalc]++;  
			    $this->refresh(); 
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Control No</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>No Of Package</th><th>No Of Pieces</th><th>Description</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $pr = Yii::$app->db->createCommand("SELECT name,rate FROM itemgroup WHERE gid ='$ck[0]'")->queryOne(0);
					  if($ck[7] == 'NOW')
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
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitemb','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$ck[3]</td><td>$pr[0]</td><td>";
					 $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[2]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $tbInvoice .= rtrim($it,' , ');
					 $tbInvoice .="</td><td>$ck[4]</td><td>$ck[5]<td>$ck[6]</td><tr>";
				
					 $i++; 
				  }
			// $tbInvoice .="<tr><td colspan=7 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 $tbInvoice .="</table>";
		return $this->render('_fboitems',['model'=>$model,'tbDet'=>$this->getCDet($cid),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
	}
	
	public function actionBnsorders()
    {
        unset($_SESSION['JobItems']);
		unset($_SESSION['JobItems2']);
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		
          $q2 ="SELECT oi.orderno as 'Order No',oi.cno as 'Control No',oi.iid,oi.id,oi.pcalc,";
		  $q2 .="CASE oi.hascbm WHEN 'Y' THEN oi.cbm ELSE 'NA' END as 'CBM',";
		  $q2 .="CASE oi.pcalc WHEN 'NOW' THEN FORMAT(price,2) ELSE 'LATER' END as 'Price USD',c.name as 'Client', ";
		  $q2 .="DATE_FORMAT(oi.cdate,'%d/%m/%Y') as 'Received Date',DATEDIFF(NOW(),oi.cdate) as 'dueon',oi.bstage,oi.gid ";
		  $q2 .="FROM orderitems oi INNER JOIN items i ON i.iid = oi.iid INNER JOIN ";
		  $q2 .="orders o ON o.orderno = oi.orderno INNER JOIN clients c ON c.cid = o.cid ";
		  $q2 .="WHERE oi.shipped = 'N' AND oi.isb = 'Y' ORDER BY oi.cdate";
		
		$cn = Yii::$app->db->createCommand($q2)->queryAll();
		$cnt = count($cn);
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Order No','Control No','Client'],],
							'pagination'=>[
							               'pageSize'=>20,
	                                      ],
						    ]);	
      
         	  
		return $this->render('movhist2',['dataProvider'=>$dataProvider]);
    }
	
	public function actionBiedits1($ono)
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
		return $this->redirect(['operation/borders2']);
	}
	
	public function actionBorders2()
	{
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $cid = $_SESSION['cid'];
		  $model = new Orderitemsb;
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
			   $qi = "INSERT INTO orderedited(gid,iid,orderno,isb,cno,hascbm,cbm,pcalc,price,cby,cdate,eby,edate) SELECT ";
			   $qi .= "gid,iid,orderno,isb,cno,hascbm,cbm,pcalc,price,cby,cdate,'$id',NOW() FROM orderitems ";
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
					  
		                $qhc ="INSERT INTO orderitems(gid,iid,orderno,isb,cno,hascbm,cbm,pcalc,price,cby,cdate)";
                        $qhc .=" VALUES('$ck[0]','$ck[4]','$orderno','Y','$ck[3]','$hascbm','$cbm','$ck[5]','$price','$id',NOW())";
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
				 $num = Yii::$app->db->createCommand("SELECT bcno FROM accode")->queryScalar();
		         Yii::$app->db->createCommand("UPDATE accode SET bcno = bcno + 1")->execute();
		         $cno = 'B-'.str_shuffle($this->getCode().$num);
				 
				$iid = implode(',',$model->iid);
                $_SESSION['JobItems'][$model->gid.':'.$model->cbm.':'.$cno.':'.$iid.':'.$model->pcalc]++;  
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
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/brmitem2','cid'=>$ckey])."</b></td>";
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
		return $this->render('_fboitems2',['model'=>$model,'tbDet'=>$this->getCDet($cid),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
	}
	
	
	public function actionBsqueeze($oid)
	{
		
		$cid = Yii::$app->db->createCommand("SELECT cid FROM orders WHERE orderno =:oid")->bindParam(':oid',$oid)->queryScalar();
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }

		  $model = new Squeeze;
		  $model2 = new Squeeze2;
		  $hasTotal = false;
		  $hasItems2 = false;
		  
		  if(isset($_POST['btnAdd2'])) 
		  {
			$model2->load(Yii::$app->request->post());
            $model2->cbm = 1;			
           	$num = Yii::$app->db->createCommand("SELECT bcno FROM accode")->queryScalar();
		         Yii::$app->db->createCommand("UPDATE accode SET bcno = bcno + 1")->execute();
		         $model2->cno = 'B-'.str_shuffle($this->getCode().$num);		  
		  if($model2->validate())
		   {
				//add this item to the session variable
                unset($_SESSION['JobItems2']);				
                $_SESSION['JobItems2'][$model2->iid.':'.$model2->cbm.':'.$model2->cno]++;   
			    $this->refresh(); 
				
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
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Control No</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>No Of Package</th><th>No Of Pieces</th><th>Description</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  $Total = 0;
				   $i = 1;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $qi = "SELECT g.name,g.rate,oi.iid,oi.hascbm,oi.cbm,oi.pcalc,oi.price,oi.cno FROM orderitems oi INNER JOIN ";
                      $qi .="itemgroup g ON g.gid = oi.gid WHERE oi.id = '$ckey'";					  
					  $ck = Yii::$app->db->createCommand($qi)->queryOne(0);
					  
					  if($ck[5] == 'NOW')
					  {
					  if($ck[3] == 'Y')
					  {
						$price = $ck[6];  
						$cbm = $ck[4];
						$calc = $ck[1]. ' X '. $ck[4];
					  }
					  else
					  {
						 $price = $ck[6];  
						 $calc = '1 X '. $ck[6];
						 $cbm = 'NA'; 
					  }
					  
					  $Total = $Total + $price;
					  $price2 = number_format($price,2);
					  }
					  else
					  {
						$cbm = 'NA'; 
						$price2 = 'Later'; 
						$calc = '';
                        if($ck[3] == 'Y')
					    { 
						$cbm = $ck[4];
					   }						
					  }
					  
					  $qs = "SELECT i.name,p.nop,p.nopieces,p.descr FROM items i INNER JOIN plist p ON i.iid = p.iid WHERE p.cno = '$ck[7]'";
					  $rst = Yii::$app->db->createCommand($qs)->queryAll(false);
					 
					  foreach($rst as $rs)
					  {
					$tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitemsq','cid'=>$ckey,'oid'=>$oid])."</b></td><td>$ck[7]</td>";
		             $tbInvoice .="<td>$ck[0]</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]<td>$rs[3]</td><tr>";
						$i++; 
					  }
					 
				  }
			// $tbInvoice .="<tr><td colspan=7 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 
				 if($_SESSION['JobItems2'])
				 {
				  $hasItems2 = true;
				  $tt2 = 0;
				  foreach($_SESSION['JobItems2'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $pr = Yii::$app->db->createCommand("SELECT name,rate FROM itemgroup WHERE gid ='$ck[0]'")->queryOne(0);
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
					  
					  $tt2 = $tt2 + $price;
					  
					//Combile All the Items Into One Group of Items That Has Been Squeezed Or Repacked
					$it = '';
					foreach($_SESSION['JobItems'] as $ckey2=>$val2)
		            {
					  $k = Yii::$app->db->createCommand("SELECT iid FROM orderitems WHERE id = '$ckey2'")->queryScalar();
					  $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($k) ORDER BY name")->queryAll(false);
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					}
				$it = rtrim($it,' , ');
			$tbInvoice .="<tr bgcolor='#D3D3D3'><th colspan=8><b>After Squeezing Details</b></th></tr>";		 
			$tbInvoice .="<tr><td>1.</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem3','cid'=>$ckey,'oid'=>$oid])."</b></td>";
		             $tbInvoice .="<td><b>New Control No:</b>&nbsp;$ck[3]</td><td>$pr[0]</td><td colspan=4>$it</td></tr>";
					 //<td>$cbm</td><td>$calc<td><b>".number_format($tt2,2)."</b></td><tr>";
					 $tbInvoice .="<tr><td colspan=8 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Fine.Save Repack Items',['operation/updatebsq','oid'=>$oid],['data'=>['confirm'=>'Submit And Save Repacked Items?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				
					 
				  }
				 }
				 
				 $tbInvoice .="</table>";
		         return $this->render('_fbsitems',['model'=>$model,'model2'=>$model2,'tbDet'=>$this->getCDet($cid,$oid,'Y'),
		                               'tbInv'=>$tbInvoice,'hasItems'=>$hasItems,'cid'=>$cid]);
		  

		  
	}
	public function actionUpdatebsq($oid)
	{
		     $Total = 0;
			 $id = Yii::$app->user->id;
	  if($_SESSION['JobItems2'])
		{	
          foreach($_SESSION['JobItems2'] as $ckey=>$val)
		        {
                 	$ck = explode(":",$ckey);
				     $pr = Yii::$app->db->createCommand("SELECT rate FROM itemgroup WHERE gid ='$ck[0]'")->queryScalar();
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
					  
		                $it = '';
						foreach($_SESSION['JobItems'] as $ckey2=>$val2)
						{
						  $cn = Yii::$app->db->createCommand("SELECT cno FROM orderitems WHERE id = '$ckey2'")->queryScalar();
						  $dt = Yii::$app->db->createCommand("SELECT iid,nop,nopieces,descr FROM plist WHERE cno = '$cn'")->queryOne(0);
						  $d = str_replace("'","''",$dt[3]);
						  $it = $dt[0];
						  $qhc ="INSERT INTO plist(iid,nop,nopieces,descr,cno) VALUES('$dt[0]','$dt[1]','$dt[2]','$d','$ck[3]')";
		                Yii::$app->db->createCommand($qhc)->execute();
						}
						
						$qhc ="INSERT INTO orderitems(gid,iid,orderno,isb,cno,hascbm,cbm,pcalc,cby,cdate,repacked)";
                        $qhc .=" VALUES('$ck[0]','$it','$oid','Y','$ck[3]','$hascbm','$cbm','LATER','$id',NOW(),'Y')";
		                Yii::$app->db->createCommand($qhc)->execute();				
                }
				
		Yii::$app->db->createCommand("UPDATE orders SET squeezed = 'Y',sqby = '$id',sqdate =NOW() WHERE orderno ='$oid'")->execute();	
                  				
		  if($_SESSION['JobItems'])
		   {	
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				     
		                $qhc ="INSERT INTO osqueezed(gid,orderno,isb,ncno,cno,iid,hascbm,cbm,pcalc,price,cby,cdate) SELECT ";
                        $qhc .="gid,orderno,'Y','$ck[3]',cno,iid,hascbm,cbm,pcalc,price,cby,cdate FROM orderitems WHERE id ='$ckey'";
		                Yii::$app->db->createCommand($qhc)->execute(); 
						Yii::$app->db->createCommand("DELETE FROM orderitems WHERE id = '$ckey'")->execute(); 
		        }

		             
					 
					  
		                
					 
		  }
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['JobItems2']);  //remove ALL chassis number to the session variable
						
			
			 Yii::$app->session->setFlash('osuccess','Customer Items has been successful Repacked');
			 $this->redirect(['operation/showordersq','oid'=>$oid,'ncno'=>$ck[3]]);
		}
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	////
	public function actionNewfcl()
	{
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		    unset($_SESSION['cid']);
			unset($_SESSION['CntAmt']);
			unset($_SESSION['Plist']);
			unset($_SESSION['sas']);
			unset($_SESSION['slid']);
			unset($_SESSION['containerno']);
			unset($_SESSION['cntsize']);
			unset($_SESSION['expsdate']);
			unset($_SESSION['expardate']);
			
		$model = new FCLShipping;
		 if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		 {
			 $_SESSION['cid'] = $model->cid;
			 $_SESSION['sas'] = $model->sas;
			 $_SESSION['slid'] = $model->slid;
			 
			 $_SESSION['containerno'] = $model->containerno;
			 $_SESSION['cntsize'] = $model->cntsize;
			 $_SESSION['expsdate'] = $model->expsdate;
			 $_SESSION['expardate'] = $model->expardate;
			 
			 return $this->redirect(['operation/fclorders']);
		 }
		 
		 return $this->render('_fclorder',['model'=>$model]);
		
	}
	
	public function actionFclorders()
	{
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $cid = $_SESSION['cid'];
		  $model = new Orderitems;
		  $model2 = new Manifest2;
		  $hasTotal = false;
		  $gid = '';

		  if(isset($_POST['btnCharge'])) 
		  {  
		  if($model2->load(Yii::$app->request->post()) && $model2->validate())
		   {
		        $num = Yii::$app->db->createCommand("SELECT cno FROM accode")->queryScalar();
				Yii::$app->db->createCommand("UPDATE accode SET cno = cno + 1")->execute();
				$cno = str_shuffle($this->getCno().$num);
				
				//add this item to the session variable 
				unset($_SESSION['CntAmt']);	
                $_SESSION['CntAmt'][$cno.':'.$model2->tamount.':'.$model2->blno]++;   
			    //$this->refresh(); 
				
		   }
          }
		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				$model->pcalc = 'LATER';
                $_SESSION['Plist'][$model->gid.':'.$model->iid.':'.$model->pcalc.':'.$model->nop.':'.$model->nopieces.':'.$model->descr]++;   
			    $model->iid =''; 
				$model->nop ='';
                $model->nopieces =''; 				
				$model->descr =''; 
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>No Of Package</th><th>No Of Pieces</th><th>Description</th></tr>";
		   $calc = '';
		   $cbm = '';
		   if($_SESSION['CntAmt'])
				 {
					 foreach($_SESSION['CntAmt'] as $key=>$va)
					 {
						$k = explode(":",$key);
                        $cno = $k[0];
						$tamt = number_format($k[1],2);
						$blno = $k[2];
					 }
				 }
		   
		   if($_SESSION['Plist'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['Plist'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitemcl','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$pr[0]</td><td>";
					 $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[2]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $tbInvoice .= rtrim($it,' , ');
					 $tbInvoice .="</td><td>$ck[4]</td><td>$ck[5]</td><td>$ck[6]</td><tr>";
				
					 $i++; 
				  }
				  
				 if($_SESSION['CntAmt'])
				 {
			 $tbInvoice .="<tr><td colspan=7 align=right>Control Number: <b>$cno</b>&nbsp;&nbsp;|&nbsp;&nbsp;BL Number <b>$blno</b>&nbsp;&nbsp;|&nbsp;&nbsp;";
			 $tbInvoice .="Total Price(USD)&nbsp;<b>$tamt</b></td></tr>";
			  $tbInvoice .="<tr><td colspan=7 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Done.Save FCL',['operation/savefcl'],['data'=>['confirm'=>'Submit And Save?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				 
				   }
				 }
				 $tbInvoice .="</table>";
		return $this->render('_fcloitems',['model'=>$model,'model2'=>$model2,'gid'=>$gid,'tbDet'=>$this->getCDet($cid),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
	}
	
	public function actionSavefcl()
	{
			$cid = $_SESSION['cid'];
			$code = 'F';
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
			 
		  if($_SESSION['CntAmt'])
		  {
			   foreach($_SESSION['CntAmt'] as $key=>$va)
		        {
					$k = explode(":",$key);
				}
		  }
		  if($_SESSION['Plist'])
		   {	
		       foreach($_SESSION['Plist'] as $ckey=>$val)
		        {
				      $ck = explode(":",$ckey);		 
					    $descr = str_replace("'","''",$ck[6]);
		                $qhc ="INSERT INTO plist(iid,nop,nopieces,descr,cno) VALUES('$ck[2]','$ck[4]','$ck[5]','$descr','$k[0]')";
		                Yii::$app->db->createCommand($qhc)->execute();
		        }

		             $gid = Yii::$app->db->createCommand("SELECT gid FROM itemgroup WHERE visible = 'N' LIMIT 1")->queryScalar();
					 $qhc ="INSERT INTO orderitems(gid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate,slid,picked,scode,shipped,";
					 $qhc .="containerno,cntsize,expsdate,expardate,sby,sdate,hasbl,bltype,blno,blby,bldate)";
					$qhc .=" VALUES('$gid','$orderno','$k[0]','N',0,'NOW','$k[1]','$id',NOW())";
		                Yii::$app->db->createCommand($qhc)->execute();
						
					 $odates = explode("/",$_SESSION['odate']);
	                 $odate = $odates[2]."-".$odates[1]."-".$odates[0];
		             
					 $sas = $_SESSION['sas'];
					 $qinv ="INSERT INTO orders(orderno,cid,sas,orderdate,ostatus,cby,cdate) "; 
                     $qinv .="VALUES('$orderno','$cid','$sas','$odate','R','$id',NOW())";
                    
		              Yii::$app->db->createCommand($qinv)->execute();
					  $cname = Yii::$app->db->createCommand("SELECT name FROM clients WHERE cid ='$cid'")->queryScalar();
					  $msg = "Received FCL Container From <b>$cname</b> And Ship as <b>$sas</b> Which Has Been Given Control #: <b>$k[0]</b>";
				        LogisticsRoles::logAction('Cargo Handling',$msg);
					  
		                unset($_SESSION['CntAmt']);  //remove ALL chassis number to the session variable
						unset($_SESSION['Plist']);
						//unset($_SESSION['cid']);
						//unset($_SESSION['sas']);
						//unset($_SESSION['odate']);
			
			 Yii::$app->session->setFlash('osuccess','Customer Items to Deliver has been successful recorded');
			 $this->redirect(['operation/showorder','oid'=>$orderno]);
		               
	          }
	
		
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function actionNeworder()
	{
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		    unset($_SESSION['cid']);
			unset($_SESSION['JobCharge']);
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
		unset($_SESSION['JobCharge']);
		$rs = Yii::$app->db->createCommand("SELECT orderno,cid,sas,orderdate FROM orders WHERE orderno ='$ono'")->queryOne(0);
		$_SESSION['orderno'] = $rs[0];
		$_SESSION['cid'] = $rs[1];
		$_SESSION['sas'] = $rs[2];
		$_SESSION['odate'] = $rs[3];
		
		//Load Mzigo Kama Ulivyokuwa Mwanzo Kabla ya Kusave
		//$model->gid.':'.$model->cbm.':'.$model->cno.':'.$iid.':'.$model->pcalc
		$q ="SELECT g.gid,g.cper,p.iid,oi.pcalc,p.nop,p.nopieces,p.descr,p.cno,oi.cbm FROM orderitems oi INNER JOIN itemgroup g ON ";
		$q .="g.gid = oi.gid INNER JOIN plist p ON oi.cno = p.cno WHERE oi.orderno = '$ono' AND oi.shipped = 'N'";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		 foreach($rst as $rs)
		 {
		  $_SESSION['JobItems'][$rs[0].':'.$rs[1].':'.$rs[2].':'.$rs[3].':'.$rs[4].':'.$rs[5].':'.$rs[6]]++;
		  $_SESSION['cno'] = $rs[7];
		  $pcal = $rs[3];
		  $cbm = $rs[8];
         }
          $_SESSION['JobCharge'][$_SESSION['cno'].':'.$pcal.':'.$cbm]++;   		 
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
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>No Of Package</th><th>No Of Pieces</th><th>Description</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  $Total = 0;
				  $i = 1;
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
					  
					  $q2 = "SELECT i.name,p.nop,p.nopieces,p.descr FROM plist p INNER JOIN items i ON i.iid = p.iid WHERE p.cno ='$ck[4]'";
					  $rst = Yii::$app->db->createCommand($q2)->queryAll(false);
					  foreach($rst as $rs)
					  {
						$tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Edit</b>",['operation/rmitemupdate','cid'=>$ckey,'ono'=>$ono,'id'=>$id])."</b></td>";
		                $tbInvoice .="<td>$ck[0]</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><tr>";
						$i++;
					  }
					 $tbInvoice .="<tr><td colspan=7 align=center>Control Number: <b>$ck[4]</b>&nbsp;&nbsp;|&nbsp;&nbsp;Total CBM: <b>$cbm</b>&nbsp;&nbsp;|&nbsp;&nbsp;Calculation&nbsp;&nbsp;|&nbsp;&nbsp;";
			 $tbInvoice .="<b>$calc</b>&nbsp;&nbsp;|&nbsp;&nbsp;Total Price(USD)&nbsp;<b>".number_format($price,2)."</b></td></tr>";
				     
				
					 $i++; 
				  }
				  $tbInvoice .="<tr><td colspan=7 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Fine.Update Items Cost ',['operation/updatecost','id'=>$id],['data'=>['confirm'=>'Submit And Save?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
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
					  
					  $rst = Yii::$app->db->createCommand("SELECT i.name FROM items i INNER JOIN plist p ON i.iid = p.iid WHERE p.cno ='$ck[4]'")->queryAll(false);
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
		  $cno = $_SESSION['cno'];
		  $model = new Orderitems;
		  $model2 = new Orderitems2;
		  $hasTotal = false;
		  $gid = '';
		  
		  if(isset($_POST['btnCharge'])) 
		  {  
		  if($model2->load(Yii::$app->request->post()) && $model2->validate())
		   {
		       // $num = Yii::$app->db->createCommand("SELECT cno FROM accode")->queryScalar();
				//Yii::$app->db->createCommand("UPDATE accode SET cno = cno + 1")->execute();
				//$cno = str_shuffle($this->getCno().$num);
				
				//add this item to the session variable 
				unset($_SESSION['JobCharge']);	
                $_SESSION['JobCharge'][$cno.':'.$model2->pcalc.':'.$model2->cbm]++;   
			    //$this->refresh(); 
				
		   }
          }
		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				$model->pcalc = 'LATER';
                $_SESSION['JobItems'][$model->gid.':'.$model->iid.':'.$model->pcalc.':'.$model->nop.':'.$model->nopieces.':'.$model->descr]++;   
			    $model->iid =''; 
				$model->nop =''; 
				$model->descr =''; 
				
		   }
          }
		  
		    $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>No Of Package</th><th>No Of Pieces</th><th>Description</th></tr>";
		   $calc = '';
		   $cbm = '';
		   if($_SESSION['JobCharge'])
				 {
					 foreach($_SESSION['JobCharge'] as $key=>$va)
					 {
						$k = explode(":",$key); 
					 }
				 }
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $gid = $ck[0];
					  $pr = Yii::$app->db->createCommand("SELECT name,rate FROM itemgroup WHERE gid ='$ck[0]'")->queryOne(0);
					  if($k[1] == 'NOW')
					  {
					  if($ck[1] == 'CBM')
					  {
						$price = $k[2] * $pr[1];  
						$cbm = $k[2];
						$calc = $k[2]. ' X '. $pr[1];
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
						$cbm = ' '; 
						$calc = 'Later'; 
                        if($ck[1] == 'CBM')
					    { 
						$cbm = $k[2];
					   }						
					  }
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem2','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$pr[0]</td><td>";
					 $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[2]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $tbInvoice .= rtrim($it,' , ');
					 $tbInvoice .="</td><td>$ck[4]</td><td>$ck[5]</td><td>$ck[6]</td><tr>";
				
					 $i++; 
				  }
				  
				 if($_SESSION['JobCharge'])
				 {
			 $tbInvoice .="<tr><td colspan=7 align=right>Control Number: <b>$cno</b>&nbsp;&nbsp;|&nbsp;&nbsp;Total CBM: <b>$cbm</b>&nbsp;&nbsp;|&nbsp;&nbsp;Calculation&nbsp;&nbsp;|&nbsp;&nbsp;";
			 $tbInvoice .="<b>$calc</b>&nbsp;&nbsp;|&nbsp;&nbsp;Total Price(USD)&nbsp;<b>".number_format($price,2)."</b></td></tr>";
			  $tbInvoice .="<tr><td colspan=7 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Done.Update Received Items ',['operation/savelco2'],['data'=>['confirm'=>'Submit And Save?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				 
				   }
				 }
				 $tbInvoice .="</table>";
		return $this->render('_foitems2',['model'=>$model,'model2'=>$model2,'gid'=>$gid,'tbDet'=>$this->getCDet($cid),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  
		  
	}
	
	public function actionSavelco()
	{
			$cid = $_SESSION['cid'];
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
			 
		  if($_SESSION['JobCharge'])
		  {
			   foreach($_SESSION['JobCharge'] as $key=>$va)
		        {
					$k = explode(":",$key);
				}
		  }
		  if($_SESSION['JobItems'])
		   {	
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				      $ck = explode(":",$ckey);
				     $pr = Yii::$app->db->createCommand("SELECT rate FROM itemgroup WHERE gid ='$ck[0]'")->queryScalar();
					 if($k[1] == 'NOW')
					 {
						  if($ck[1] == 'CBM')
						  {
							$price = $k[2] * $pr;  
							$cbm = $k[2];
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
					    $descr = str_replace("'","''",$ck[6]);
		                $qhc ="INSERT INTO plist(iid,nop,nopieces,descr,cno) VALUES('$ck[2]','$ck[4]','$ck[5]','$descr','$k[0]')";
		                Yii::$app->db->createCommand($qhc)->execute();
		        }

		             
					 $qhc ="INSERT INTO orderitems(gid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate)";
					$qhc .=" VALUES('$ck[0]','$orderno','$k[0]','$hascbm','$cbm','$k[1]','$price','$id',NOW())";
		                Yii::$app->db->createCommand($qhc)->execute();
						
					 $odates = explode("/",$_SESSION['odate']);
	                 $odate = $odates[2]."-".$odates[1]."-".$odates[0];
		             
					 $sas = $_SESSION['sas'];
					 $qinv ="INSERT INTO orders(orderno,cid,sas,orderdate,ostatus,cby,cdate) "; 
                     $qinv .="VALUES('$orderno','$cid','$sas','$odate','R','$id',NOW())";
                    
		              Yii::$app->db->createCommand($qinv)->execute();
					  $cname = Yii::$app->db->createCommand("SELECT name FROM clients WHERE cid ='$cid'")->queryScalar();
					  $msg = "Received Cargo From <b>$cname</b> And Ship as <b>$sas</b> Which Has Been Given Control #: <b>$k[0]</b>";
				        LogisticsRoles::logAction('Cargo Handling',$msg);
					  
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['JobCharge']);
						//unset($_SESSION['cid']);
						//unset($_SESSION['sas']);
						//unset($_SESSION['odate']);
			
			 Yii::$app->session->setFlash('osuccess','Customer Items to Deliver has been successful recorded');
			 $this->redirect(['operation/showorder','oid'=>$orderno]);
		               
	          }
	
		
	}
	
	public function actionSavelco2()
	{
			$cid = $_SESSION['cid'];
			$orderno = $_SESSION['orderno'];
			$code = 'S';
			
		     $Total = 0;
			 $id = Yii::$app->user->id;
			 $num = Yii::$app->db->createCommand("SELECT cno FROM accode")->queryScalar();
				Yii::$app->db->createCommand("UPDATE accode SET cno = cno + 1")->execute();
				$cno2 = str_shuffle($this->getCno().$num);
			 
		  if($_SESSION['JobCharge'])
		  {
			   foreach($_SESSION['JobCharge'] as $key=>$va)
		        {
					$k = explode(":",$key);
				}
		  }
		  if($_SESSION['JobItems'])
		   {
             $qi = "INSERT INTO orderedited(gid,iid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate,eby,edate) SELECT ";
			   $qi .= "gid,iid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate,'$id',NOW() FROM orderitems ";
			   $qi .= "WHERE orderno = '$orderno' AND shipped = 'N'";
			   Yii::$app->db->createCommand($qi)->execute();
               Yii::$app->db->createCommand("DELETE FROM orderitems WHERE orderno = '$orderno' AND shipped = 'N'")->execute(); 
               	   
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				      $ck = explode(":",$ckey);
				     $pr = Yii::$app->db->createCommand("SELECT rate FROM itemgroup WHERE gid ='$ck[0]'")->queryScalar();
					 if($k[1] == 'NOW')
					 {
						  if($ck[1] == 'CBM')
						  {
							$price = $k[2] * $pr;  
							$cbm = $k[2];
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
					    $descr = str_replace("'","''",$ck[6]);
		                $qhc ="INSERT INTO plist(iid,nop,nopieces,descr,cno) VALUES('$ck[2]','$ck[4]','$ck[5]','$descr','$cno2')";
		                Yii::$app->db->createCommand($qhc)->execute();
		        }

		             
					 $qhc ="INSERT INTO orderitems(gid,orderno,cno,hascbm,cbm,pcalc,price,cby,cdate)";
					$qhc .=" VALUES('$ck[0]','$orderno','$cno2','$hascbm','$cbm','$k[1]','$price','$id',NOW())";
		                Yii::$app->db->createCommand($qhc)->execute();
						
					
                    
		              Yii::$app->db->createCommand($qinv)->execute();
					  $cname = Yii::$app->db->createCommand("SELECT name FROM clients WHERE cid ='$cid'")->queryScalar();
					  $msg = "Received Cargo From <b>$cname</b> And Ship as <b>$sas</b> Which Has Been Updated And Given Control #: <b>$k[0]</b>";
				        LogisticsRoles::logAction('Cargo Handling',$msg);
					  
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['JobCharge']);
						//unset($_SESSION['cid']);
						//unset($_SESSION['sas']);
						//unset($_SESSION['odate']);
			
			 Yii::$app->session->setFlash('osuccess','Customer Items to Deliver has been successful Updated');
			 $this->redirect(['operation/showorder','oid'=>$orderno]);
		               
	          }
	
		
	}
	
	
	public function actionOrders()
	{
		
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $cid = $_SESSION['cid'];
		  $model = new Orderitems;
		  $model2 = new Orderitems2;
		  $hasTotal = false;
		  $gid = '';

		  if(isset($_POST['btnCharge'])) 
		  {  
		  if($model2->load(Yii::$app->request->post()) && $model2->validate())
		   {
		        $num = Yii::$app->db->createCommand("SELECT cno FROM accode")->queryScalar();
				Yii::$app->db->createCommand("UPDATE accode SET cno = cno + 1")->execute();
				$cno = str_shuffle($this->getCno().$num);
				
				//add this item to the session variable 
				unset($_SESSION['JobCharge']);	
                $_SESSION['JobCharge'][$cno.':'.$model2->pcalc.':'.$model2->cbm]++;   
			    //$this->refresh(); 
				
		   }
          }
		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				$model->pcalc = 'LATER';
                $_SESSION['JobItems'][$model->gid.':'.$model->iid.':'.$model->pcalc.':'.$model->nop.':'.$model->nopieces.':'.$model->descr]++;   
			    $model->iid =''; 
				$model->nop ='';
                $model->nopieces =''; 				
				$model->descr =''; 
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
           $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Item Group</th>";
		   $tbInvoice .="<th>Items</th><th>No Of Package</th><th>No Of Pieces</th><th>Description</th></tr>";
		   $calc = '';
		   $cbm = '';
		   if($_SESSION['JobCharge'])
				 {
					 foreach($_SESSION['JobCharge'] as $key=>$va)
					 {
						$k = explode(":",$key); 
					 }
				 }
		   
		   if($_SESSION['JobItems'])
				 {
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					  $gid = $ck[0];
					  $pr = Yii::$app->db->createCommand("SELECT name,rate FROM itemgroup WHERE gid ='$ck[0]'")->queryOne(0);
					  if($k[1] == 'NOW')
					  {
					  if($ck[1] == 'CBM')
					  {
						$price = $k[2] * $pr[1];  
						$cbm = $k[2];
						$calc = $k[2]. ' X '. $pr[1];
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
						$cbm = ' '; 
						$calc = 'Later'; 
                        if($ck[1] == 'CBM')
					    { 
						$cbm = $k[2];
					   }						
					  }
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['operation/rmitem','cid'=>$ckey])."</b></td>";
		             $tbInvoice .="<td>$pr[0]</td><td>";
					 $rst = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($ck[2]) ORDER BY name")->queryAll(false);
					  $it = '';
					  foreach($rst as $rs)
					  {
						  $it .= $rs[0].' , ';
						
					  }
					  $tbInvoice .= rtrim($it,' , ');
					 $tbInvoice .="</td><td>$ck[4]</td><td>$ck[5]</td><td>$ck[6]</td><tr>";
				
					 $i++; 
				  }
				  
				 if($_SESSION['JobCharge'])
				 {
			 $tbInvoice .="<tr><td colspan=7 align=right>Control Number: <b>$cno</b>&nbsp;&nbsp;|&nbsp;&nbsp;Total CBM: <b>$cbm</b>&nbsp;&nbsp;|&nbsp;&nbsp;Calculation&nbsp;&nbsp;|&nbsp;&nbsp;";
			 $tbInvoice .="<b>$calc</b>&nbsp;&nbsp;|&nbsp;&nbsp;Total Price(USD)&nbsp;<b>".number_format($price,2)."</b></td></tr>";
			  $tbInvoice .="<tr><td colspan=7 align=right><b>".Html::a('<i class="glyphicon glyphicon-ok"></i> Done.Click Here To Receive ',['operation/savelco'],['data'=>['confirm'=>'Submit And Save?','method' => 'post',],'class'=>'btn green btn-xs'])."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
				 
				   }
				 }
				 $tbInvoice .="</table>";
		return $this->render('_foitems',['model'=>$model,'model2'=>$model2,'gid'=>$gid,'tbDet'=>$this->getCDet($cid),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
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
			   if($exp[0] == 7)
			   {
			    $q = "SELECT iid as id,name FROM items ORDER BY name";
			   }
			   else
			   {
				 $q = "SELECT iid as id,name FROM items  WHERE gid ='$exp[0]' ORDER BY name";   
			   }
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
	public function actionRmitemcl($cid)
	{
	   unset($_SESSION['Plist'][$cid]);
	    return $this->redirect(['fclorders']);
	}
	public function actionBrmitem2($cid)
	{
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['borders2']);
	}
	public function actionRmitemb($cid)
	{
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['borders']);
	}
	
	public function actionRmitemsq($cid,$oid)
	{
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['squeeze','oid'=>$oid]);
	}
	
	public function actionRmcntbl($cid)
	{
	   unset($_SESSION['CntCargos'][$cid]);
	    return $this->redirect(['manifest']);
	}
	
	public function actionRmbellow($cid,$ono,$id)
	{
	   unset($_SESSION['JobB'][$cid]);
	    return $this->redirect(['bprocessing','ono'=>$ono,'id'=>$id]);
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
	public function actionRmbl($cid)
	{
	    unset($_SESSION['JobItems2'][$cid]);
	    return $this->redirect(['manifest']);
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
		
		  $qo = "SELECT oi.cno,oi.iid,oi.hascbm,oi.cbm,oi.price,g.rate,g.name,oi.pcalc FROM orderitems oi INNER JOIN itemgroup g ON ";
		  $qo .="g.gid = oi.gid WHERE oi.cno =:ncno ";
		  
		  $order = Yii::$app->db->createCommand($qo)->bindParam(':ncno',$ncno)->queryOne(false);
	
		if(!empty($order))
		{ 
	        $i = 1;
			$Total = 0;
			
			$tbO .="<table class='table table-bordered table-gray footable'><thead><tr><th colspan=6><b>ITEMS DESCRIPTIONS</b></th></tr>";
		    $tbO .="<tr><th>SN</th><th>Item Group</th><th>Items</th><th>No Of Items</th><th>No Of Pieces</th><th>Description</th></tr></thead>";
		     
			$q2 = "SELECT i.name,p.nop,p.nopieces,p.descr FROM plist p INNER JOIN items i ON i.iid = p.iid WHERE p.cno = '$order[0]'";
			$rst = Yii::$app->db->createCommand($q2)->queryAll(false);
					  
		     foreach($rst as $rs)
			  {
			     
				 $tbO .="<tr><td>$i</td><td>$order[6]</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td></tr>";
				 $i++;
			  }
				if($order[7] == 'NOW')
                {					
			     if($order[2] == 'Y')
					  {
						$price = number_format($order[3] * $order[5],2);  
						$cbm = $order[3];
						$calc = $order[3]. ' X '. $order[5];
					  }
					  else
					  {
						 $price = number_format($order[5],2);  
						 $calc = '1 X '. $order[5];
						 $cbm = 'NA'; 
					  }
				}
				else
				{
					$price = 'Later';  
				    $calc = 'Later';
					 $cbm = 'NA'; 
				}
					  
				 $tbO .="<tr><td colspan=6 align=center><i>Control Number: <b>$order[0]</b>&nbsp;&nbsp;|&nbsp;&nbsp;Total CBM:<b>$cbm</b>&nbsp;&nbsp;|&nbsp;&nbsp;Calculations: <b>$calc</b>";
			   $tbO .="&nbsp;&nbsp;|&nbsp;&nbsp;Total: <b>$price2</b></i></th></tr>";
		    }
			$tbO .="</table>";
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
			 $tbH .="<b>Order DATE : </b>$dt[9]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>".Html::a('<i class="glyphicon glyphicon-download"></i> Receive Next Cargo Form Same Client',['operation/orders'])."</b></td></tr>";
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
		
		  $qo = "SELECT oi.cno,g.name,oi.hascbm,oi.cbm,oi.pcalc,oi.price,g.rate FROM orderitems oi INNER JOIN ";
		  $qo .="itemgroup g ON g.gid = oi.gid WHERE oi.orderno =:oid ";
		  
		  $order = Yii::$app->db->createCommand($qo)->bindParam(':oid',$oid)->queryOne(false);
	
		if(!empty($order))
		{ 
	        $i = 1;
			$Total = 0;
			
			$tbO .="<table class='table table-bordered table-gray footable'><thead><tr><th colspan=6><b>ITEMS DESCRIPTIONS</b></th></tr>";
		    $tbO .="<tr><th>SN</th><th>Item Group</th><th>Items</th><th>No Of Items</th><th>No Of Pieces</th><th>Description</th></tr></thead>";
			
			$cno = Yii::$app->db->createCommand("SELECT cno FROM orderitems WHERE orderno =:oid")->bindParam(':oid',$oid)->queryAll(false);
			foreach($cno as $cn)
			{
			$q2 = "SELECT i.name,p.nop,p.nopieces,p.descr FROM plist p INNER JOIN items i ON i.iid = p.iid WHERE p.cno = '$cn[0]'";
			
			$rst = Yii::$app->db->createCommand($q2)->queryAll(false);
					  
		     foreach($rst as $rs)
			  {
			     
				 $tbO .="<tr><td>$i</td><td>$order[1]</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td></tr>";
				 $i++;
			  }
			  if($order[4] == 'NOW')
				{					
					 if($order[2] == 'Y')
						  {
							$price = $order[5];  
							$cbm = $order[3];
							$calc = $order[3]. ' X '. $order[6];
						  }
						  else
						  {
							 $price = $order[5];  
							 $calc = '1 X '. $order[6];
							 $cbm = 'NA'; 
						  }
						  
						  $price2 = number_format($price,2);
				}
				else
				{
					if($order[3] == 'Y')
						  {
							$price2 = 'Later'; //$order[3] * $order[5];  
							$cbm = $order[3];
							$calc = 'Later'; //$order[3]. ' X '. $order[5];
						  }
						  else
						  {
							 $price2 = 'Later'; //$order[5];  
							 $calc = 'Later'; //'1 X '. $order[5];
							 $cbm = 'NA'; 
						  }
						  
				}
			   $tbO .="<tr><td colspan=6 align=center><i>Control Number: <b>$order[0]</b>&nbsp;&nbsp;|&nbsp;&nbsp;Total CBM:<b>$cbm</b>&nbsp;&nbsp;|&nbsp;&nbsp;Calculations: <b>$calc</b>";
			   $tbO .="&nbsp;&nbsp;|&nbsp;&nbsp;Total: <b>$price2</b></i></th></tr>";
		    }
			$tbO .="</table>";
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

	public function actionRcargo($q = null) 
	 {
		$q = strtolower($q);	
		
	   $qd ="SELECT oi.cno FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno ";
	   $qd .="INNER JOIN clients c ON o.cid = c.cid WHERE oi.shipped = 'N' AND c.name LIKE '%$q%' UNION ";
	   
	   $qd .="SELECT oi.cno FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno ";
	   $qd .="INNER JOIN clients c ON o.cid = c.cid WHERE oi.shipped = 'N' AND c.phone LIKE '%$q%' UNION ";
	 
	   $qd .="SELECT oi.cno FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno ";
	   $qd .="INNER JOIN clients c ON o.cid = c.cid WHERE oi.shipped = 'N' AND oi.cno LIKE '%$q%' ";
	
		
		$data = Yii::$app->db->createCommand($qd)->queryAll(false);
		$out = [];
		foreach ($data as $d) {
			$out[] = ['value' => $d[0]];
		}
		echo Json::encode($out);
    }
	
	public function actionSas()
	{
		if(!LogisticsRoles::isOperation())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		
		$model = new Sas;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		   Yii::$app->session->setFlash('rsuccess',"Shipping Name Registered Successfuly.");
		   return $this->refresh();
        }
		
        return $this->render('_fsas',['model'=>$model,'tbC'=>$this->getSas()]);
	}
	
	public function getSas()
	{
		$query = "SELECT c.name,s.sas,s.id FROM sas s INNER JOIN clients c ON s.cid = c.cid";
		$data = Yii::$app->db->createCommand($query)->queryAll(false);
		$tbC = "";
		if(!empty($data))
		{ 
	        $i = 1;
			$tbC .="<table class='table table-striped table-bordered'>";
		    $tbC .="<tr><th>SN</th><th>CUSTOMER</th><th>SHIPPING AS</th><th>ACTION</th></tr>";
		     foreach($data as $d)
			  {
			    
				$tbC .="<tr><td>$i</td><td>$d[0]</td><td>$d[1]</td>";
				$tbC .="<td><b>".Html::a('Edit',['operation/editsas', 'id'=>$d[2]])."</b></td></tr>";
				$i++;
			  }
			   $tbC .="</table>";
		  }
		  
		return $tbC;
	}

	protected function loadSas($id)
    {
        if (($model = Sas::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function actionEditsas($id)
	{
		$model = $this->loadSas($id);
		
		if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		   Yii::$app->session->setFlash('rsuccess',"Shipping Name Updated Successfuly.");
		   return $this->redirect(['operation/sas']);
        }
		
        return $this->render('_fsas',['model'=>$model,'tbC'=>$this->getSas()]);
	}

	public function actionGetsas()
	{
		 if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $pid = $parents[0];
			   $q = "SELECT sas as id,sas as name FROM sas WHERE cid ='$pid' ORDER BY sas";
           	   $data = Yii::$app->db->createCommand($q)->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
	}
		function getCno() 
	{
		$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < 4; $i++) 
		{
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function actionTosession($id)
	{
		//echo $id; exit;
		
		$_SESSION['JobItems'][$id]++;
		return $this->redirect(['operation/loading']);
	}

}
