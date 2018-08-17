<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Items;
use app\models\Trace;
use app\models\Products;
use app\models\FrostanRoles;
use yii\helpers\Html;
use yii\helpers\Json;
use kartik\mpdf\Pdf;

class BscrptController extends Controller
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

	public function actionViewrpt($rid)
	{
	  
	    if(isset($_POST['btnReport']))
        {
			$npar = $_POST['txtNPar'];
			if($npar == 1)
            {
        	    $pvalue1 = $_POST['txtPar1'];
				$functname = $_POST['txtFunctname'];
	            return $this->{$functname}($pvalue1);
            }
			elseif($npar == 2)
			{
				$pvalue1 = $_POST['from'];
	            $pvalue2 = $_POST['to'];
	            $functname = $_POST['txtFunctname'];
	            return $this->{$functname}($pvalue1,$pvalue2);      
			}
			elseif($npar == 3)
			{
				$pvalue1 = $_POST['txtPar1'];
	            $pvalue2 = $_POST['from'];
				$pvalue3 = $_POST['to'];
	            $functname = $_POST['txtFunctname'];
	            return $this->{$functname}($pvalue1,$pvalue2,$pvalue3);      
			}
			else
			{
				$pvalue1 = $_POST['from'];
	            $pvalue2 = $_POST['to'];
	            $functname = $_POST['txtFunctname'];
	            return $this->{$functname}($pvalue1,$pvalue2);     
			}
        } 
        else
        {
			$qrp ="select parameter,p1,p1caption,p2,p2caption,p3,p3caption,label,method FROM reportsmenu WHERE id='$rid'";
			$rowp = Yii::$app->db->createCommand($qrp)->queryOne(0);
		    if($rowp[0] == 2)
		    {
				return $this->render('viewrpt',['title'=>$rowp[7],'fname'=>$rowp[8]]);
			}
			elseif($rowp[0] == 3)
			{
			   if($rowp[1] == 'user')
			   {
				   return $this->render('viewrpt3',array('title'=>$rowp[7],'fname'=>$rowp[8]));
			   }
			   else
			   {
				   return $this->render('viewrpt1',array('title'=>$rowp[7],'fname'=>$rowp[8]));
			   }   
			}
			else
			{
				return $this->{$rowp[8]}();
			}
		}	 
		
	}
	
	public function Employee($empid)
	{
		$query = "SELECT CONCAT(fname,IFNULL(mname,''),sname) FROM employees WHERE empid = '$empid' ";
		$employee = Yii::$app->db->createCommand($query)->queryScalar();
		return $employee;
	}
	
	public function getClients()
	{
		$data = array();
		$rst = Yii::$app->db->createCommand("SELECT cid,name FROM clients ORDER BY name ")->queryAll(false);
		 $data['all'] = 'All';
		foreach($rst as $rs)
		{
			 $data[$rs[0]] = $rs[1];
		}
		
		return $data;
	}
	
	public function getEmps()
	{
		$data = array();
		$rst = Yii::$app->db->createCommand("SELECT empid,CONCAT(fname,' ',mname,' ',sname) FROM employees WHERE empid !='1001' ORDER BY fname ")->queryAll(false);
		 $data['all'] = 'All';
		foreach($rst as $rs)
		{
			 $data[$rs[0]] = $rs[1];
		}
		
		return $data;
	}
	
	public function allusers($from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		$qs = "SELECT e.empcode,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),e.gender,e.email,j.name,e.telno,z.name,e.cby,DATE_FORMAT(DATE(e.cdate),'%d/%m/%Y') ";
		$qs.="FROM employees e INNER JOIN jobtitles j ON e.titleid = j.titleid INNER JOIN zones z ON e.zid = z.zid WHERE e.status = 'A' AND DATE(e.cdate) BETWEEN '$f' AND '$t' ";
      
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=10><center><b>A LIST OF REGISTERED USERS FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>EMP CODE</b></td><td><b>NAME</b></td><td><b>GENDER</b></td><td><b>EMAIL</b></td><td><b>TITTLE</b></td><td><b>PHONE</b></td><td><b>ZONE</b></td><td><b>REGISTERED BY</b></td><td><b>REGISTERED DATE</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]<td>$rs[5]</td><td>$rs[6]</td><td>".$this->Employee($rs[7])."</td><td>$rs[8]</td></tr>";
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Registered Users'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	
	
	public function allsuppliers($from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		$qs = "SELECT supcode,NAME,suptype,address,paddress,phone,email,DATE_FORMAT(regdate,'%d/%m/%Y') FROM suppliers WHERE STATUS = 'A' ";
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=9><center><b>A LIST OF REGISTERED USERS FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>SUP CODE</b></td><td><b>NAME</b></td><td><b>TYPE</b></td><td><b>ADDRESS</b></td><td><b>PHYSICAL ADDRESS</b></td><td><b>PHONE</b></td><td><b>EMAIL</b></td><td><b>REGISTERED DATE</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]<td>$rs[5]</td><td>$rs[6]</td><td>$rs[7]</td></tr>";
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Registered Suppliers'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function igroups($from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		$qs = "SELECT i.name,i.cper,i.rate,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),DATE_FORMAT(i.cdate,'%d/%m/%Y') FROM itemgroup i ";
		$qs .= "INNER JOIN employees e ON i.cby = e.empid WHERE DATE(i.cdate) BETWEEN '$f' AND '$t' ";
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=6><center><b>A LIST OF REGISTERED ITEM GROUPS FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>GROUP</b></td><td><b>UNIT OF MEASURE</b></td><td><b>RATE PER UNIT(USD)</b></td><td><b>REGISTERED BY</b></td><td><b>REGISTERED DATE</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]</td></tr>";
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Registered Item Groups'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function allslines($from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		$qs = "SELECT NAME,phone,email,address FROM sline WHERE DATE(cdate) BETWEEN '$f' AND '$t' ";
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=5><center><b>A LIST OF REGISTERED SHIPPING LINES FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>NAME</b></td><td><b>PHONE</b></td><td><b>EMAIL</b></td><td><b>ADDRESS</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td></tr>";
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Registered Shipping Lines'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	
	public function idetails($from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		$qe = "SELECT i.gid,i.name FROM itemgroup i";
		$igroups = Yii::$app->db->createCommand($qe)->queryAll(false);
		if(!empty($igroups))
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			$logo = "<table width=100%>";
			$logo .= "<tr><td><center>$log</center></td></tr>";
			$logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			$logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			$logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
				
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=7><center><b>A LIST OF REGISTERED DETAILED ITEMS FROM $from TO $to</b></center></td></tr>";
			$tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>ITEM GROUP</b></td><td><b>ITEMS</b></td><td><b>UNIT OF MEASURE</b></td><td><b>RATE PER UNIT (USD)</b></td><td><b>REGISTERED BY</b></td><td><b>DATE REGISTERED</b></td></tr>";
			
			$i=1;
			foreach($igroups as $ig)
			{
				$qc = "SELECT COUNT(iid) FROM items WHERE gid = '$ig[0]' ";
				$span = Yii::$app->db->createCommand($qc)->queryScalar();
			
				$qs = "SELECT it.name,it.cper,it.rate,CONCAT(e.fname,' ',IFNULL(e.mname,''),e.sname),DATE_FORMAT(DATE(it.cdate),'%d/%m/%Y') FROM items ";
				$qs .="it INNER JOIN employees e ON it.cby = e.empid WHERE it.gid = '$ig[0]' AND DATE(it.cdate) BETWEEN '$f' AND '$t'";

				$rst = Yii::$app->db->createCommand($qs)->queryAll(false);	
				
				$tbTr .="<tr><td rowspan='$span'>$i</td><td rowspan='$span'>$ig[1]</td>";
				
				foreach ($rst as $rs) 
				{
					$tbTr .="<td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]</td></tr><tr>";
				}
				$i++;
			}
			$tbTr .="</table>";
		}
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Registered Detailed Items'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function oplogs($user,$from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		if($user == 'all')
		{
			$qs = "SELECT CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),o.ipaddress,o.module,o.task,DATE_FORMAT(o.cdate,'%d/%m/%Y'),o.ctime FROM oplogs o ";
			$qs .="INNER JOIN employees e ON o.empid = e.empid WHERE DATE(o.cdate) BETWEEN '$f' AND '$t'";
		}
		else
		{
			$qs = "SELECT CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),o.ipaddress,o.module,o.task,DATE_FORMAT(o.cdate,'%d/%m/%Y'),o.ctime FROM oplogs o ";
			$qs .="INNER JOIN employees e ON o.empid = e.empid WHERE o.empid = '$user' AND DATE(o.cdate) BETWEEN '$f' AND '$t'";
		}
		
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=7><center><b>OPERATIONS PEFORMED FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>USER</b></td><td><b>IP ADDRESS</b></td><td><b>MODULE</b></td><td><b>TASK PEFORMED</b></td><td><b>DATE</b></td><td><b>TIME</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]</td><td>$rs[5]</td></tr>";
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['System Operation Logs'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function rcargo($user,$from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		if($user == 'all')
		{
			$qs = "SELECT c.name,o.sas,DATE_FORMAT(o.orderdate,'%d/%m/%Y'),i.name,oi.iid,oi.cno,oi.hascbm,oi.cbm,oi.pcalc,oi.price FROM orders o INNER JOIN";
			$qs .=" orderitems oi ON o.orderno = oi.orderno INNER JOIN clients c ON o.cid = c.cid INNER JOIN itemgroup i ON oi.gid = i.gid ";
			$qs .="WHERE o.ostatus = 'R' AND DATE(o.orderdate) BETWEEN '$f' AND '$t'";

		}
		else
		{
			$qs = "SELECT c.name,o.sas,DATE_FORMAT(o.orderdate,'%d/%m/%Y'),i.name,oi.iid,oi.cno,oi.hascbm,oi.cbm,oi.pcalc,oi.price FROM orders o INNER JOIN";
			$qs .=" orderitems oi ON o.orderno = oi.orderno INNER JOIN clients c ON o.cid = c.cid INNER JOIN itemgroup i ON oi.gid = i.gid ";
			$qs .="WHERE o.ostatus = 'R' AND o.cid = '$user' AND DATE(o.orderdate) BETWEEN '$f' AND '$t'";
		}
		
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=9><center><b>RECEIVED CARGO FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>CLIENT</b></td><td><b>SHIPPING AS</b></td><td><b>DATE RECEIVED</b></td><td><b>ITEM GROUP</b></td><td><b>CARGO ITEMS</b></td><td><b>CONTROL NO</b></td><td><b>MEASUREMENT</b></td><td><b>PRICE (USD)</b></td></tr>";
			$i = 1;
			$TotalPrice;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
				
				$items = explode(',',$rs[4]);
				$j = 0;
				$cargoItems = "";
				foreach($items as $item)
				{
					$crg = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item' ")->queryScalar();
					$cargoItems .= $crg.',';
					$j++;
				}
				//$cargoItems .= ")";
				$tbTr .="<td>$cargoItems</td><td>$rs[5]</td>";
				
				if($rs[6] == 'Y')
				{
					$tbTr .="<td>$rs[7] - CBM</td>";
				}
				else
				{
					$tbTr .="<td>BUNDLE</td>";
				}
				
				if($rs[8] == 'NOW')
				{
					$tbTr .="<td>$rs[9]</td>";
					$TotalPrice = $TotalPrice + $rs[9];
				}
				else
				{
					$tbTr .="<td>LATER</td>";
				}
				
				$tbTr .="</tr>";
				$i++;
            }
			$tbTr .="<tr><td colspan=8><center>Total</center></td><td>$TotalPrice</td></tr>";
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Received Cargo'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function scargo($user,$from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		if($user == 'all')
		{
			$qs = "SELECT c.name,r.sas,i.name,o.iid,o.cno,o.hascbm,o.cbm,o.pcalc,o.price,s.name,o.scode,o.containerno,";
			$qs .="CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),DATE_FORMAT(o.sdate,'%d/%m/%Y'),DATE_FORMAT(o.expsdate,'%d/%m/%Y'),";
			$qs .="DATE_FORMAT(o.expardate,'%d/%m/%Y')FROM orderitems o INNER JOIN orders r ON o.orderno = r.orderno INNER JOIN clients c ON r.cid = c.cid ";
			$qs .="INNER JOIN  itemgroup i ON o.gid = i.gid INNER JOIN sline s ON o.slid = s.slid INNER JOIN employees e ON o.sby = e.empid WHERE ";
			$qs .="o.shipped = 'Y' AND DATE(o.sdate) BETWEEN '$f' AND '$t'";

		}
		else
		{
			$qs = "SELECT c.name,r.sas,i.name,o.iid,o.cno,o.hascbm,o.cbm,o.pcalc,o.price,s.name,o.scode,o.containerno,";
			$qs .="CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),DATE_FORMAT(o.sdate,'%d/%m/%Y'),DATE_FORMAT(o.expsdate,'%d/%m/%Y'),";
			$qs .="DATE_FORMAT(o.expardate,'%d/%m/%Y')FROM orderitems o INNER JOIN orders r ON o.orderno = r.orderno INNER JOIN clients c ON r.cid = c.cid ";
			$qs .="INNER JOIN  itemgroup i ON o.gid = i.gid INNER JOIN sline s ON o.slid = s.slid INNER JOIN employees e ON o.sby = e.empid WHERE ";
			$qs .="o.shipped = 'Y' AND r.cid = '$user' AND DATE(o.sdate) BETWEEN '$f' AND '$t'";
		}
		
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=14><center><b>SHIPPED CARGO FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>CLIENT</b></td><td><b>SHIPPING AS</b></td><td><b>ITEM GROUP</b></td>";
			$tbTr .="<td><b>ITEMS</b></td><td><b>CONTROL NO</b></td><td><b>MEASUREMENT</b></td><td><b>SHIPPING LINE</b></td>";
			$tbTr .="<td><b>SHIPPING CODE</b></td><td><b>CONTAINER NO</b></td><td><b>SHIPPED BY</b></td><td><b>DATE SHIPPED</b></td><td><b>EXPECTED ARRIVAL DATE</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td>";
				
				$items = explode(',',$rs[3]);
				$j = 0;
				$cargoItems = "";
				foreach($items as $item)
				{
					$crg = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item' ")->queryScalar();
					$cargoItems .= $crg.',';
					$j++;
				}
				//$cargoItems .= ")";
				$tbTr .="<td>$cargoItems</td><td>$rs[4]</td>";
				
				if($rs[5] == 'Y')
				{
					$tbTr .="<td>$rs[6] - CBM</td>";
				}
				else
				{
					$tbTr .="<td>BUNDLE</td>";
				}
				
				//if($rs[7] == 'NOW')
				//{
					//$tbTr .="<td>$rs[8]</td>";
				//}
				//else
				//{
					//$tbTr .="<td>LATER</td>";
				//}
				
				$tbTr .="<td>$rs[9]</td><td>$rs[10]</td><td>$rs[11]</td><td>$rs[12]</td><td>$rs[13]</td><td>$rs[14]</td></tr>";
				
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Shipped Cargo'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function ccargo($user,$from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		if($user == 'all')
		{
			$qs = "SELECT c.name,o.sas,o.orderno,i.name,oi.iid,oi.cno,oi.hascbm,oi.cbm,oi.price,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),";
			$qs .="DATE_FORMAT(oi.cldate,'%d/%m/%Y') FROM orders o INNER JOIN orderitems oi ON o.orderno = oi.orderno INNER JOIN clients c ON ";
			$qs .="o.cid = c.cid INNER JOIN itemgroup i ON oi.gid = i.gid INNER JOIN employees e ON oi.clby = e.empid WHERE oi.cleared = 'Y' ";
			$qs .="AND DATE(oi.cldate) BETWEEN '$f' AND '$t'";
		}
		else
		{
			$qs = "SELECT c.name,o.sas,o.orderno,i.name,oi.iid,oi.cno,oi.hascbm,oi.cbm,oi.price,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),";
			$qs .="DATE_FORMAT(oi.cldate,'%d/%m/%Y') FROM orders o INNER JOIN orderitems oi ON o.orderno = oi.orderno INNER JOIN clients c ON ";
			$qs .="o.cid = c.cid INNER JOIN itemgroup i ON oi.gid = i.gid INNER JOIN employees e ON oi.clby = e.empid WHERE oi.cleared = 'Y' ";
			$qs .="AND c.cid = '$user' AND DATE(oi.cldate) BETWEEN '$f' AND '$t'";
		}
		
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=11><center><b>CLEARED/DISPATCHED CARGO FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>CLIENT</b></td><td><b>SHIPPING AS</b></td><td><b>ORDER NO</b></td>";
			$tbTr .="<td><b>ITEM GROUP</b></td><td><b>ITEMS</b></td><td><b>CONTROL NO</b></td><td><b>MEASUREMENT</b></td>";
			$tbTr .="<td><b>CLEARED BY</b></td><td><b>CLEARED DATE</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
				
				$items = explode(',',$rs[4]);
				$j = 0;
				$cargoItems = "";
				foreach($items as $item)
				{
					$crg = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item' ")->queryScalar();
					$cargoItems .= $crg.',';
					$j++;
				}
				//$cargoItems .= ")";
				$tbTr .="<td>$cargoItems</td><td>$rs[5]</td>";
				
				if($rs[6] == 'Y')
				{
					$tbTr .="<td>$rs[7] - CBM</td>";
				}
				else
				{
					$tbTr .="<td>BUNDLE</td>";
				}
				
				$tbTr .="<td>$rs[9]</td><td>$rs[10]</td></tr>";
				
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Cleared/Dispatched Cargo'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function sqcargo1($from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		$qs = "SELECT o.orderno,i.name,os.cno,os.ncno,os.iid,oi.iid,oi.hascbm,os.hascbm,oi.cbm,os.cbm,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),";
		$qs .="DATE_FORMAT(os.cdate,'%d/%m/%Y') FROM orders o INNER JOIN orderitems oi ON o.orderno = oi.orderno INNER JOIN osqueezed os ON oi.cno = os.ncno ";
		$qs .="INNER JOIN employees e ON os.cby = e.empid INNER JOIN itemgroup i ON oi.gid = i.gid WHERE o.squeezed = 'Y' AND DATE(os.cdate) BETWEEN '$f' AND '$t'";
		
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=11><center><b>SQUEEZED/REPACKED CARGO FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>ORDER NO</b></td><td><b>ITEM GROUP</b></td><td><b>OLD CONTROL NO</b></td>";
			$tbTr .="<td><b>NEW CONTROL NO</b></td><td><b>BEFORE SQUEEZE</b></td><td><b>AFTER SQUEEZ</b></td><td><b>PREVIOUS MEASURE</b></td><td><b>CURRENT MEASURE</b></td>";
			$tbTr .="<td><b>SQUEEZED BY</b></td><td><b>DATE SQUEEZED</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
				
				$items = explode(',',$rs[4]);
				$j = 0;
				$cargoItems = "Cargo Items (";
				foreach($items as $item)
				{
					$crg = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item' ")->queryScalar();
					$cargoItems .= $crg.',';
					$j++;
				}
				$cargoItems .= ")";
				$tbTr .="<td>$cargoItems</td>";
				
				$items1 = explode(',',$rs[5]);
				$k = 0;
				$cargoItems1 = "Cargo Items (";
				foreach($items1 as $item1)
				{
					$crg1 = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item1' ")->queryScalar();
					$cargoItems1 .= $crg1.',';
					$k++;
				}
				$cargoItems1 .= ")";
				$tbTr .="<td>$cargoItems1</td>";
				
				if($rs[6] == 'Y')
				{
					$tbTr .="<td>$rs[8] - CBM</td>";
				}
				else
				{
					$tbTr .="<td>BUNDLE</td>";
				}
				
				if($rs[7] == 'Y')
				{
					$tbTr .="<td>$rs[9] - CBM</td>";
				}
				else
				{
					$tbTr .="<td>BUNDLE</td>";
				}
				
				$tbTr .="<td>$rs[10]</td><td>$rs[11]</td></tr>";
				
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Squeezed/Repacked Cargo'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function allclients($from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		$qs = "SELECT c.name,c.address,c.paddress,c.ctype,CONCAT('+','',c.pcode,'',c.phone),c.email,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),";
		$qs.="DATE_FORMAT(c.cdate,'%d/%m/%Y') FROM clients c INNER JOIN employees e ON c.cby =  e.empid WHERE DATE(c.cdate) BETWEEN '$f' AND '$t' ";
      
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=9><center><b>A LIST OF REGISTERED CLIENTS FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>NAME</b></td><td><b>POSTAL ADDRESS</b></td><td><b>PHYSICAL ADDRESS</b></td><td><b>CLIENT TYPE</b></td><td><b>PHONE NO</b></td><td><b>EMAIL</b></td><td><b>REGISTERED BY</b></td><td><b>REGISTERED DATE</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td><td>$rs[4]<td>$rs[5]</td><td>$rs[6]</td><td>$rs[7]</td></tr>";
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Registered Clients'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function sqcargo($from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		$qr = "SELECT cno,repacked FROM orderitems WHERE repacked = 'Y' AND DATE(cdate) BETWEEN '$f' AND '$t' ";
		$cnos = Yii::$app->db->createCommand($qr)->queryAll(false);
		if(!empty($cnos))
		{ 
	
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			$logo = "<table width=100%>";
			$logo .= "<tr><td><center>$log</center></td></tr>";
			$logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			$logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			$logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
			
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			//$tbTr1 .="<tr><td colspan=$idad><center><b>SQUEEZED/REPACKED CARGO FROM $from TO $to</b></center></td></tr>";
			$tbTr .="<tr  bgcolor='#CACCCE'><td colspan=20><center><b>SQUEEZED/REPACKED CARGO FROM $from TO $to</b></center></td></tr>";
			$tb = "";
			$i = 1;
			foreach($cnos as $cno)
			{ 
				$qcount = "SELECT COUNT(id) FROM osqueezed WHERE ncno = '$cno[0]' ";
				$idadi = Yii::$app->db->createCommand($qcount)->queryScalar();
				
				$q = "SELECT os.iid,os.cno,os.hascbm,os.cbm,os.pcalc,os.price FROM osqueezed os INNER JOIN itemgroup i ON ";
				$q .="os.gid = i.gid WHERE ncno = '$cno[0]' ";
				$rst = Yii::$app->db->createCommand($q)->queryAll(false);
				
				$qs = "SELECT o.orderno,i.name,o.iid,o.cbm,o.price,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),DATE_FORMAT(o.cdate,'%d/%m/%Y') FROM ";
				$qs .="orderitems o INNER JOIN itemgroup i ON o.gid = i.gid INNER JOIN employees e ON o.cby = e.empid WHERE cno = '$cno[0]' ";
				$rst1 = Yii::$app->db->createCommand($qs)->queryOne(0);
				
				
				
				//$idad = $idadi + 6;
				
				$tbTr1 .="<tr><td><b>SN</b></td><td><b>ORDER NO</b></td><td><b>ITEM GROUP</b></td>";
				for($j = 1; $j <= $idadi; $j++)
				{
					$tbTr1 .= "<td><b>CARGO $j BEFORE SQUEEZ</b></td>";
				}
				
				$tbTr1 .="<td><b>AFTER SQUEEZ</b></td><td><b>SQUEEZED BY</b></td><td><b>DATE SQUEEZED</b></td></tr>";
				
				$tbTr1 .="<tr><td>$i</td><td>$rst1[0]</td><td>$rst1[1]</td>";
				
				foreach ($rst as $rs) 
				{
					$items = explode(',',$rs[0]);
					$cargoItems = "Items [";
					foreach($items as $item)
					{
						$crg = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item' ")->queryScalar();
						$cargoItems .= $crg.',';
					}
					$cargoItems .= "]";
					$tbTr1 .="<td>$cargoItems , Control No : $rs[1] , ";
				
					if($rs[2] == 'Y')
					{
						$tbTr1 .="Measurement : $rs[3] - CBM , ";
					}
					else
					{
						$tbTr1 .="Measurement - BUNDLE , ";
					}
				
					if($rs[4] == 'NOW')
					{
						$tbTr1 .="Price : $rs[5]</td>";
					}
					else
					{
						$tbTr1 .="Price : LATER</td>";
					}
				}

				$items1 = explode(',',$rst1[2]);
				$cargoItems1 = "Items [";
				foreach($items1 as $item1)
				{
					$crg1 = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item1' ")->queryScalar();
					$cargoItems1 .= $crg1.',';
				}
				$cargoItems1 .= "]";
				$tbTr1 .="<td>$cargoItems1 , New Control No : $cno[0] , ";
				
				if($rs[2] == 'Y')
				{
					$tbTr1 .="Measurement : $rst1[3] - CBM , ";
				}
				else
				{
				$tbTr1 .="Measurement : BUNDLE , ";
				}
			
				if($rs[4] == 'NOW')
				{
					$tbTr1 .="Price : $rst1[4]</td>";
				}
				else
				{
					$tbTr1 .="Price : LATER</td>";
				}
			
				$tbTr1 .="<td>$rst1[5]</td><td>$rst1[6]</td></tr></table><br /><table cellpadding=1 border=1 cellspacing=0 width=100%>";
				$tb .= $tbTr1;
				$i++;
            }
			//echo $tbTr; exit;
			$tbTr .= $tbTr1;
            $tbTr .="</table>";
        }
		  
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		//echo $tbTr; exit;
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Squeezed/Repacked Cargo'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function pforccargo($user,$from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		if($user == 'all')
		{
			$qs = "SELECT oi.orderno,c.name,o.sas,i.name,oi.iid,oi.price,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),DATE_FORMAT(DATE(oi.pricesdate),'%d/%m/%Y') ";
			$qs.="FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno  INNER JOIN clients c ON o.cid = c.cid INNER JOIN itemgroup i ON ";
			$qs .="oi.gid = i.gid INNER JOIN employees e ON oi.pricesby = e.empid WHERE oi.paid = 'Y' AND oi.picked = 'Y' AND DATE(c.cdate) BETWEEN '$f' AND '$t' ";
		}
		else
		{
			$qs = "SELECT oi.orderno,c.name,o.sas,i.name,oi.iid,oi.price,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),DATE_FORMAT(DATE(oi.pricesdate),'%d/%m/%Y') ";
			$qs.="FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno  INNER JOIN clients c ON o.cid = c.cid INNER JOIN itemgroup i ON ";
			$qs .="oi.gid = i.gid INNER JOIN employees e ON oi.pricesby = e.empid WHERE oi.paid = 'Y' AND oi.picked = 'Y' AND c.cid = '$user' AND DATE(c.cdate) BETWEEN '$f' AND '$t' ";
		}
	  
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=9><center><b>A LIST OF PAID FOR AND PICKED CARGO FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>ORDER NO</b></td><td><b>CLIENT</b></td><td><b>SHIPPING AS</b></td><td><b>ITEM GROUP</b></td>";
			$tbTr .="<td><b>ITEMS</b></td><td><b>PRICE(USD)</b></td><td><b>RECEIVED BY</b></td><td><b>RECEVED DATE</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
				
				$items = explode(',',$rs[4]);
				$cargoItems = "";
				foreach($items as $item)
				{
					$crg = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item' ")->queryScalar();
					$cargoItems .= $crg.',';
				}
				$tbTr .="<td>$cargoItems</td>";
				
				$tbTr .="<td>$rs[5]<td>$rs[6]</td><td>$rs[7]</td></tr>";
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Paid For And Picked Cargo'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
	
	public function pfornccargo($user,$from,$to)
	{
		$fr = explode('/',$from);
		$f = $fr[2].'-'.$fr[1].'-'.$fr[0];
		
		$tt = explode('/',$to);
		$t = $tt[2].'-'.$tt[1].'-'.$tt[0];
		
		$query = "SELECT cname,paddress,box,telephone,mob,fax,region,vat,tin,email,website FROM companyinfo WHERE cid = 3";
		$r = Yii::$app->db->createCommand($query)->queryOne(0);
		
		if($user == 'all')
		{
			$qs = "SELECT oi.orderno,c.name,o.sas,i.name,oi.iid,oi.price,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),DATE_FORMAT(DATE(oi.pricesdate),'%d/%m/%Y') ";
			$qs.="FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno  INNER JOIN clients c ON o.cid = c.cid INNER JOIN itemgroup i ON ";
			$qs .="oi.gid = i.gid INNER JOIN employees e ON oi.pricesby = e.empid WHERE oi.paid = 'Y' AND oi.picked = 'N' AND DATE(c.cdate) BETWEEN '$f' AND '$t' ";
		}
		else
		{
			$qs = "SELECT oi.orderno,c.name,o.sas,i.name,oi.iid,oi.price,CONCAT(e.fname,' ',IFNULL(e.mname,''),' ',e.sname),DATE_FORMAT(DATE(oi.pricesdate),'%d/%m/%Y') ";
			$qs.="FROM orderitems oi INNER JOIN orders o ON oi.orderno = o.orderno  INNER JOIN clients c ON o.cid = c.cid INNER JOIN itemgroup i ON ";
			$qs .="oi.gid = i.gid INNER JOIN employees e ON oi.pricesby = e.empid WHERE oi.paid = 'Y' AND oi.picked = 'N' AND c.cid = '$user' AND DATE(c.cdate) BETWEEN '$f' AND '$t' ";
		}
	  
		$rst = Yii::$app->db->createCommand($qs)->queryAll(false);
        if (!empty($rst)) 
		{
			$log ="<img src='".Yii::getAlias('@web') .'/img/biglogo.png'."' width ='160' height ='100'><br /><br />";
			 $logo = "<table width=100%>";
			 $logo .= "<tr><td><center>$log</center></td></tr>";
			 $logo .= "<tr><td><center>P.O.BOX $r[2]/Dar Es Salaam/Tanzania</center></td></tr>";
			 $logo .="<tr><td><center>Phone: $r[3] / Mobile: $r[4]</center></td></tr>";
			 $logo .="<tr><td><center><b>TIN: $r[8] VRN: $r[7]</center></td></tr></table><br >";
		    
			$tbTr = '<table cellpadding=1 border=1 cellspacing=0 width=100%>';
			$tbTr .="<tr><td colspan=9><center><b>A LIST OF PAID FOR BUT NOT PICKED CARGO FROM $from TO $to</b></center></td></tr>";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>SN</b></td><td><b>ORDER NO</b></td><td><b>CLIENT</b></td><td><b>SHIPPING AS</b></td><td><b>ITEM GROUP</b></td>";
			$tbTr .="<td><b>ITEMS</b></td><td><b>PRICE(USD)</b></td><td><b>RECEIVED BY</b></td><td><b>RECEVED DATE</b></td></tr>";
			$i = 1;
            foreach ($rst as $rs) 
			{
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2]</td><td>$rs[3]</td>";
				
				$items = explode(',',$rs[4]);
				$cargoItems = "";
				foreach($items as $item)
				{
					$crg = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid = '$item' ")->queryScalar();
					$cargoItems .= $crg.',';
				}
				$tbTr .="<td>$cargoItems</td>";
				
				$tbTr .="<td>$rs[5]<td>$rs[6]</td><td>$rs[7]</td></tr>";
				$i++;
            }
            $tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table><br />&nbsp;<br />&nbsp;<br />";
		}
		
		date_default_timezone_set('Africa/Nairobi');
			
			$pdf = new Pdf([
        
        				//'mode' => Pdf::MODE_CORE, 
						'mode' => Pdf::MODE_UTF8,
       					 'format' => Pdf::FORMAT_A4, 
        				'orientation' => Pdf::ORIENT_LANDSCAPE, 
        				'destination' => Pdf::DEST_BROWSER, 
       					 'content' => $logo.$tbTr,   
       					 'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
        					'methods' => [ 
            					'SetHeader'=>['Paid For But Not Picked Cargo'], 
            					'SetFooter'=>['Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}'],
        					]
   					 ]);
			 return $pdf->render();
	}
}