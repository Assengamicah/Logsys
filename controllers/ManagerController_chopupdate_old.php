<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\data\SqlDataProvider;
use yii\filters\VerbFilter;
use app\models\Chops;
use app\models\Chops2;
use app\models\Chops3;
use app\models\Chops4;
use app\models\Barcodes;
use app\models\RBarcodes;
use app\models\Items;
use app\models\Trace;
use app\models\Products;
use app\models\FrostanRoles;
use yii\helpers\Html;
use yii\helpers\Json;

class ManagerController extends Controller
{
	public $menu = 'managermenu';

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
	 
	//BARCODE GENERATOR
	public function actionBcode()
	{
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		$model = new Barcodes;
        if($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		    $pid = explode(":",$model->prodid);
		    $num = Yii::$app->db->createCommand("SELECT brno FROM accode")->queryScalar();
		    Yii::$app->db->createCommand("UPDATE accode SET brno = brno + 1")->execute();
		    $refid = str_shuffle($this->getCode().$num);
			$max = Yii::$app->db->createCommand("SELECT COUNT(id) FROM barcodes WHERE prodid = '$pid[0]'")->queryScalar();
			for($i = 1; $i <= $model->quantity; $i++)
			{
		     $bcode = $pid[1].($max + $i);
			 $qi = "INSERT INTO barcodes(prodid,barcode,used,refid) VALUES('$pid[0]','$bcode','N','$refid')"; 
		     Yii::$app->db->createCommand($qi)->execute();
			}
		  return $this->redirect(['manager/pbarcodes','refid'=>$refid]);
        }
		
        return $this->render('_fbcode',['model'=>$model]);
		  
		
	}
	
	public function actionRbarcode()
	{
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		  
		$model = new RBarcodes;
        if($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		    $pid = explode(":",$model->prodid);
		    
		  return $this->redirect(['manager/rpbarcodes','pid'=>$pid[0]]);
        }
		
        return $this->render('_frbcode',['model'=>$model]);
		  
		
	}
	
	public function actionRpbarcodes($pid)
	{
		$i = 1;
		//$this->layout = 'min-master';
		$items = Yii::$app->db->createCommand("SELECT barcode FROM barcodes WHERE prodid ='$pid' AND used ='N'")->queryAll(false);
		$qp = "SELECT name FROM products WHERE prodid ='$pid'";
		$pname = Yii::$app->db->createCommand($qp)->queryScalar();
		return $this->renderAjax('vwbcode',['items'=>$items,'pname'=>$pname]);
		
	}
	
	public function actionPbarcodes($refid)
	{
		$i = 1;
		//$this->layout = 'min-master';
		$items = Yii::$app->db->createCommand("SELECT barcode FROM barcodes WHERE refid ='$refid'")->queryAll(false);
		$qp = "SELECT DISTINCT p.name FROM products p INNER JOIN barcodes b ON p.prodid = b.prodid WHERE b.refid ='$refid'";
		$pname = Yii::$app->db->createCommand($qp)->queryScalar();
		return $this->renderAjax('vwbcode',['items'=>$items,'pname'=>$pname]);
		
	}
	
	
	
	
	
	////////////////////////////////////////////////////////////////////////////////////
	//Printing Cutting Instruction and Updating after cutting Done
	
	public function actionPcinst()
    {
        
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['manager/noaccess']);
		  }
		
          $q2 ="SELECT DISTINCT s.name as Supplier,o.orderno as 'Order No',c.cdate as 'Instruction Date',";
		  $q2 .="c.instrcode as 'Reference No' FROM chops c INNER JOIN orderitems o ON o.id = c.oprodid INNER JOIN orders od ON ";
		  $q2 .="od.orderno = o.orderno INNER JOIN suppliers s ON s.supid = od.supid WHERE c.printed = 'N'";

		  
		$cn = Yii::$app->db->createCommand($q2)->queryAll();
		$cnt = count($cn);

		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Supplier','Order No','Instruction Date','Reference No'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);	
      
         	  
		return $this->render('vwpcinst',['dataProvider'=>$dataProvider]);
    }
	
	public function actionPcinst2()
    {
        
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['manager/noaccess']);
		  }
		
          $q2 ="SELECT DISTINCT s.name as Supplier,o.orderno as 'Order No',c.cdate as 'Instruction Date',";
		  $q2 .="c.instrcode as 'Reference No' FROM chops c INNER JOIN orderitems o ON o.id = c.oprodid INNER JOIN orders od ON ";
		  $q2 .="od.orderno = o.orderno INNER JOIN suppliers s ON s.supid = od.supid WHERE c.printed = 'Y'";

		  
		$cn = Yii::$app->db->createCommand($q2)->queryAll();
		$cnt = count($cn);

		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Supplier','Order No','Instruction Date','Reference No'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);	
      
         	  
		return $this->render('vwpcinst2',['dataProvider'=>$dataProvider]);
    }
	
	public function actionPrintco($cid,$isnew)
	{
		
		if($isnew == 'Y')
		{
		$id = Yii::$app->user->id;
		$q = "UPDATE cinstruction SET printed = 'Y',pby = '$id', pdate = CURDATE(), ptime = CURTIME() WHERE cicode ='$cid'";
		Yii::$app->db->createCommand($q)->execute();
		}
		$qp = "SELECT o.orderno,ot.batchno,s.supcode,s.name,DATE_FORMAT(ot.chopdate,'%d/%d/%Y') FROM orderitems ot ";
            $qp .= "INNER JOIN orders o ON o.orderno = ot.orderno INNER JOIN suppliers s ON ";
            $qp .="s.supid = o.supid WHERE ot.cicode ='$cid' LIMIT 1";
			$dt = Yii::$app->db->createCommand($qp)->queryOne(false);
			
		
			$q = "SELECT p.name,c.oprodid,c.quantity,COUNT(c.cicode) FROM cinstruction c INNER JOIN products p ON p.prodid = c.oprodid ";
            $q .= "WHERE c.cicode ='$cid' GROUP BY p.name,c.oprodid ";
        
        $rst = Yii::$app->db->createCommand($q)->queryAll(false);
        if (!empty($rst)) 
		{
             $logo ="<img src='".Yii::getAlias('@web') .'/img/frostan.png'."' width ='160' height ='50'><br /><br />";
			 $tbH = "<table cellpadding=1 border=1 cellspacing=0 width=100% >";
			// $tbH .= "<tr><td colspan=8><img src='".Yii::getAlias('@web') .'/img/frostan.png'."' width ='235' height ='56'></td></tr>";
			 $tbH .="<tr><td colspan=7 ><b>PRIMAL CUTTING PREPARATION FORM IIB</b></td><td>&nbsp; Document # :<b>$cid</b></td></tr>";
			 $tbH .= "<tr><td><b>DATE</b></td><td>$dt[4]</td><td><b>ORDER NO</b></td><td>$dt[0]</td>";
			 $tbH .= "<td><b>BATCH NO</b></td><td>$dt[1]</td><td><b>SUPPLIER</b></td><td>$dt[3]</td></tr></table><br >";
			 
		    
			$tbTr = "<table cellpadding=1 border=1 cellspacing=0 width=100% >";
            $tbTr .="<tr bgcolor='#CACCCE'><td><b>PRODUCT</b></td><td><b>CUT</b></td><td><b>SPEC</b></td>";
            $tbTr .="<td><b>AFTER CUT WEIGHT</b></td><td><b>% CARCASS</b></td><td><b>FQ%</b></td></tr>";
			
            foreach ($rst as $rs) 
			{
                $tbTr .="<tr><td rowspan=$rs[3]><b>$rs[0] ($rs[2])</b></td>";

                 $q2 = "SELECT p.name,c.spec FROM cinstruction c INNER JOIN products p ";
                $q2 .="ON p.prodid = c.prodid WHERE c.cicode ='$cid' AND c.oprodid ='$rs[1]'";

                $acts = Yii::$app->db->createCommand($q2)->queryAll(false);
				$sum = 0;
                foreach ($acts as $act) 
				{
				 // $sum = $sum + $act[2];
                  $tbTr .="<td>$act[0]</td><td>$act[1]</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
                    $tbTr .="</tr><tr>";
                }
				// $sumBig = $sumBig + $sum;
	            // $tbTr .="<tr><td align=right colspan=3><b>Sub Total:&nbsp;</b></td><th>".number_format($sum,2)." KG</th><td colspan=3>&nbsp;</td></tr>";
            }
          //  $tbTr .="<tr><td align=right colspan=3><b>Grand Total:&nbsp;</b></td><th>".number_format($sumBig,2)." KG</th>";
			//$tbTr .="<td colspan=3>&nbsp;</td></tr></table>";
			$tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table>";
		}
		
            date_default_timezone_set('Africa/Nairobi');
		    $pdf = Yii::$app->pdf;
			//$pdf->orientation = ORIENT_LANDSCAPE;
			$pdf->methods->setHeader = 'Cutting Preparation Sheet';
			$pdf->methods->setFooter = 'Generated on: {DATE d/m/Y h:i:s},Page # {PAGENO}';
            $pdf->content = $logo.$tbH.$tbTr;
            return $pdf->render();
		   
			/*$pdf = Yii::$app->pdf; 
			$mpdf = $pdf->api;
			$mpdf->SetHeader('Mzigo');
			$stylesheet = file_get_contents(Yii::getAlias('@webroot').'/css/pdfrpt.css');
	        $mpdf->WriteHTML($stylesheet,1);
			$mpdf->WriteHtml($tbH.$tbTr);
			echo $mpdf->Output(); */
		
		//return $this->render('vwcut',['tbData'=>$tbH.$tbTr]);
	}
	
	public function actionAcupdate()
	{
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		unset($_SESSION['instrcode']); 
		$model = new Chops3;
        if($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		    
			$_SESSION['instrcode'] = $model->instrcode;
		  return $this->redirect(['manager/chopupdate']);
        }
		
        return $this->render('_fpcupdate',['model'=>$model]);
	}
	
	public function actionChopupdate()
	{
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		
		$model = new Chops4;
        if($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		    $model->doupdate();
			Yii::$app->session->setFlash('psuccess',"After Cut Details Successful Recorded.");
			
		   $model->nweight = '';
		   $model->fq = '';
		   $model->barcode = '';
		   $model->carcass = '';
        }
		
        return $this->render('_fpcupdate2',['model'=>$model,'tbData'=>$this->getUpdatedP()]);
		
	}
	
	public function actionCupdate2($cid)
	{
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
		
		$model = $this->loadChop($cid);
		$model->prodid = $model->id;
        if($model->load(Yii::$app->request->post()) && $model->validate()) 
		{ 
		   $model->doupdate();
		   Yii::$app->session->setFlash('psuccess',"After Cut Record Updated Successful.");
		   return $this->redirect(['chopupdate']);
        }
		
        return $this->render('_fpcupdate2',['model'=>$model,'tbData'=>$this->getUpdatedP()]);
		
	}
	
	protected function loadChop($id)
    {
        if (($model = Chops4::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
	
	public function getUpdatedP()
	{		 
		$tbData ='';
		$nat = $_SESSION['instrcode'];
		$q = "SELECT c.barcode,CONCAT(p.name,' ',c.spec),c.nweight,c.fq,c.id FROM chops c INNER JOIN products p ON ";
		$q .="p.prodid = c.prodid  WHERE c.instrcode = '$nat' AND c.status = 'A' AND c.nweight IS NOT NULL ";
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		if(!empty($rst))
		{ 
		  $tbData .= "<table class='table table-bordered table-gray'><thead><tr><th>SN</th><th>BARCODE</th><th>PRODUCT</th>";
		  $tbData .= "<th>WEIGHT(KG)</th><th>FQ%</th><th>ACTION</th></tr></thead>";
		   $i = 1;
		    foreach($rst as $rs)
		    {
			  $tbData .="<tr><td>$i</td><td>$rs[0]</td><td>$rs[1]</td><td>$rs[2] KG</td><td>$rs[3]</td>";
			  $tbData .="<td>&nbsp;".Html::a("<b>Edit</b>",['manager/cupdate2','cid'=>$rs[4]])."</td></tr>";
			  $i++;
		    }
			$tbData .="</table>";
		}
		
			return $tbData;
		
		
		
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////
	
    public function actionIndex()
    {
        
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['admin/noaccess']);
		  }
       $model = new Trace;
		$tbTrace = '';
        if($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		  $tbTrace = $model->getTraces();
        }	  
		return $this->render('index',['model'=>$model,'tbTrace'=>$tbTrace]);
    }
	
	
	public function actionShowcutting($cid)
	{
		    $qp = "SELECT o.orderno,ot.batchno,s.supcode,s.name,DATE_FORMAT(ot.chopdate,'%d/%d/%Y') FROM orderitems ot ";
            $qp .= "INNER JOIN orders o ON o.orderno = ot.orderno INNER JOIN suppliers s ON ";
            $qp .="s.supid = o.supid WHERE ot.cicode ='$cid' LIMIT 1";
			$dt = Yii::$app->db->createCommand($qp)->queryOne(false);
			
		
			$q = "SELECT p.name,c.oprodid,c.quantity,COUNT(c.cicode) FROM cinstruction c INNER JOIN products p ON p.prodid = c.oprodid ";
            $q .= "WHERE c.cicode ='$cid' GROUP BY p.name,c.oprodid ";
 
        
        $rst = Yii::$app->db->createCommand($q)->queryAll(false);
        if (!empty($rst)) 
		{
            
			 $tbH = "<table class='table table-bordered'><tr><td colspan=8><b>PRIMAL CUTTING PREPARATION FORM IIB</b></td></tr>";
			 $tbH .="<td><b>".Html::a("<b>Print</b>",['printco','cid'=>$cid,'isnew'=>'Y'],['target'=>'_blank'])."</b></td></tr>";
			 $tbH .= "<tr><td><b>DATE</b></td><td>$dt[4]</td><td><b>ORDER NO</b></td><td>$dt[0]</td>";
			 $tbH .= "<td><b>BATCH NO</b></td><td>$dt[1]</td><td><b>SUPPLIER</b></td><td>$dt[3]</td></tr></table>";
			 
		    
			$tbTr = "<table class='table table-bordered table-gray'><thead>";
            $tbTr .="<tr><th>PRODUCT</th><th>CUT</th><th>SPEC</th></th><th>AFTER CUT WEIGHT</th>";
            $tbTr .="<th>% CARCASS</th><th>FQ%</th></tr></thead>";
			
            foreach ($rst as $rs) 
			{
                $tbTr .="<tr><td rowspan=$rs[3]><b>$rs[0] ($rs[2])</b></td>";

                $q2 = "SELECT p.name,c.spec FROM cinstruction c INNER JOIN products p ";
                $q2 .="ON p.prodid = c.prodid WHERE c.cicode ='$cid' AND c.oprodid ='$rs[1]'";

                $acts = Yii::$app->db->createCommand($q2)->queryAll(false);
				$sum = 0;
                foreach ($acts as $act) 
				{
				 // $sum = $sum + $act[2];
                  $tbTr .="<td>$act[0]</td><td>$act[1]</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
                    $tbTr .="</tr><tr>";
                }
				// $sumBig = $sumBig + $sum;
	            // $tbTr .="<tr><td align=right colspan=3><b>Sub Total:&nbsp;</b></td><th>".number_format($sum,2)." KG</th><td colspan=3>&nbsp;</td></tr>";
            }
          //  $tbTr .="<tr><td align=right colspan=3><b>Grand Total:&nbsp;</b></td><th>".number_format($sumBig,2)." KG</th>";
			//$tbTr .="<td colspan=3>&nbsp;</td></tr></table>";
			$tbTr .="</table>";
        }
		else
		{
			$tbTr = "<table><tr><td><b>Invalid Request</td></tr></table>";
		}
		
		return $this->render('vwcut',['tbData'=>$tbH.$tbTr]);
	}
		
	public function actionCinstr() 
	{
        if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		unset($_SESSION['orderno']);
		unset($_SESSION['JobItems']);
		$model = new Chops;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
		{
		  $_SESSION['orderno'] = $model->orderno;
		  Yii::$app->db->createCommand("UPDATE orderitems SET cweight = 0 WHERE orderno = '$model->orderno'")->execute();
		  return $this->redirect(['manager/cinstructions']);
        }
		
        return $this->render('_fcins',['model'=>$model]);
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
	public function actionCinstructions()
	{
		
		if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		  $orderno = $_SESSION['orderno'];
		  $model = new Chops2;
		  $hasTotal = false;
		  
		  if(isset($_POST['btnGeneral'])) 
		  {
			$num = Yii::$app->db->createCommand("SELECT cpno FROM accode")->queryScalar();
		    Yii::$app->db->createCommand("UPDATE accode SET cpno = cpno + 1")->execute();
		    $cicode = str_shuffle($this->getCode().$num);

			 $id = Yii::$app->user->id;
		  if($_SESSION['JobItems'])
		   {	
		       foreach($_SESSION['JobItems'] as $ckey=>$val)
		        {
				      $ck = explode(":",$ckey);
				     
					    $tweight = $ck[1] * $ck[4];
				$qu ="SELECT rwunit FROM orderitems WHERE prodid ='$ck[0]' AND rweight = '$ck[1]' AND orderno = '$orderno' LIMIT 1";
				$unit = Yii::$app->db->createCommand($qu)->queryScalar();
				
		                $qhc ="INSERT INTO cinstruction(cicode,oprodid,prodid,quantity,spec,tweight,twunit,cby,cdate,ctime)";
                        $qhc .=" VALUES('$cicode','$ck[0]','$ck[2]','$ck[4]','$ck[3]','$tweight','$unit','$id',CURDATE(),CURTIME())";
		                Yii::$app->db->createCommand($qhc)->execute();
						
						$qhc ="SELECT COUNT(id) FROM orderitems WHERE prodid ='$ck[0]' AND rweight = '$ck[1]' ";
						  $qhc .="AND orderno = '$orderno' AND chopped = 'Y' AND DATE(chopdate) = CURDATE()";
				          $alc = Yii::$app->db->createCommand($qhc)->queryScalar();
						if($alc == 0)
						{
						for($i = 0; $i < $ck[4]; $i++)
						{
                          $qi ="SELECT id FROM orderitems WHERE prodid ='$ck[0]' AND rweight = '$ck[1]' ";
						  $qi .="AND orderno = '$orderno' AND status = 'R' AND chopped = 'N' LIMIT 1";
				          $theid = Yii::$app->db->createCommand($qi)->queryScalar();
						 
						 $q ="UPDATE orderitems SET chopped = 'Y',cicode ='$cicode',chopdate = CURDATE() WHERE id ='$theid'";
						  Yii::$app->db->createCommand($q)->execute();
						}
						}
		        }
		
					  
		                unset($_SESSION['JobItems']);  //remove ALL chassis number to the session variable
						unset($_SESSION['orderno']);
						
			
			 Yii::$app->session->setFlash('osuccess','Cutting Preparation Has been Successful Created');
			 $this->redirect(['manager/showcutting','cid'=>$cicode]);
		               
	          }
	
			 
							 
		  }
		  if(isset($_POST['btnAdd'])) 
		  {  
		  if($model->load(Yii::$app->request->post()) && $model->validate())
		   {
				//add this item to the session variable 
				$_SESSION['JobItems'][$model->oprodid.':'.$model->prodid.':'.$model->spec.':'.$model->quantity]++; 
				//$qu = "UPDATE orderitems SET cweight = cweight + $model->weight WHERE id ='$model->oprodid'";
                //Yii::$app->db->createCommand($qu)->execute();		
			   // $this->refresh(); 
			  // $model->quantity = '';
			   $model->spec = '';
			    $model->prodid = '';
				
		   }
          }
		  
		   $i = 1;
		   $Total = 0;
		   $tbInvoice = "<table class='table table-bordered'><tr><th>SN</th><th>Option</th><th>Product</th><th>Product To Cut</th>";
		   $tbInvoice .="<th>Cut</th><th>Spec</th></tr>";
		   
		   if($_SESSION['JobItems'])
				 {
				  $rw = count($_SESSION['JobItems']);	 
				  $hasItems = true;
				  foreach($_SESSION['JobItems'] as $ckey=>$val)
		          {
					  $ck = explode(":",$ckey);
					 // $Total = $Total + $ck[3];
					  $qp ="SELECT p.name FROM products p INNER JOIN orderitems o ON p.prodid = o.prodid WHERE o.prodid ='$ck[0]' AND ";
					  $qp .="o.rweight = '$ck[1]' AND o.orderno = '$orderno' LIMIT 1";
					  $prod = Yii::$app->db->createCommand($qp)->queryScalar();
					  $cut = Yii::$app->db->createCommand("SELECT name FROM products WHERE prodid ='$ck[2]'")->queryScalar();
					 
				     $tbInvoice .="<tr><td>$i</td><td><b>".Html::a("<b>Remove</b>",['manager/rmitem','cid'=>$ckey])."</b></td>";
					 $tbInvoice .="<td>$prod</td><td align=center>$ck[4]</td><td>$cut</td><td>$ck[3]</td></tr>";
					$i++; 
				  }
			// $tbInvoice .="<tr><td colspan=5 align=right><b>Total</b></td><td><b>".number_format($Total,2)."</b></td></tr>";
				 }
				 $tbInvoice .="</table>";
		return $this->render('_fcproducts',['model'=>$model,'tbDet'=>$this->getSDet($orderno),'tbInv'=>$tbInvoice,'hasItems'=>$hasItems]);
		  

		  
	}
	
	public function actionGetcuts()
	{
		$data = [];
		$nat = $_SESSION['orderno'];
         if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $pid = $parents[0];
			   $exp = explode(":",$pid);
			   //$q = "SELECT itemid,prodid FROM orderitems WHERE id = '$pid'";
			  // $iid = Yii::$app->db->createCommand($q)->queryOne(0);
			   
			   $q = "SELECT itemid,prodid FROM orderitems WHERE orderno = '$nat' AND prodid = '$exp[0]' AND rweight = '$exp[1]' LIMIT 1";
			   $iid = Yii::$app->db->createCommand($q)->queryOne(0);
			   
			   
			   $q = "SELECT prodid as id,name FROM products  WHERE itemid ='$iid[0]' AND prodid != '$iid[1]'";
           	   $data = Yii::$app->db->createCommand($q)->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
	}
	
	public function actionGetcproducts()
	{
		$data = [];
         if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $nat = $parents[0];
			   $q = "SELECT o.id,p.name as name FROM products p INNER JOIN orderitems o ON p.prodid = o.prodid  WHERE ";
			   $q .="o.orderno = '$nat' AND o.chopped = 'N' AND o.status = 'R'";
           	   $data = Yii::$app->db->createCommand($q)->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
	}
	
	
	public function actionGetploc()
	{
		$data = [];
         if (isset($_POST['depdrop_parents'])) 
		 {
           $parents = $_POST['depdrop_parents'];
            if ($parents != null) 
			{
               $nat = $parents[0];
			   $q = "SELECT o.id,CONCAT(p.name,' : ',l.name) as name FROM products p INNER JOIN orderitems o ON ";
			   $q .="p.prodid = o.prodid  INNER JOIN locations l ON l.locid = o.locid ";
			   $q .="WHERE o.orderno = '$nat' AND o.status = 'R' AND o.rweight > 0";
           	   $data = Yii::$app->db->createCommand($q)->queryAll();
               echo Json::encode(['output'=>$data, 'selected'=>'']);
              return ;
            }
         }
    echo Json::encode(['output'=>'', 'selected'=>'']);
	}
	
	public function actionRmitem($cid)
	{
	  // $ck = explode(":",$cid);
	  // $qr = "UPDATE orderitems SET cweight = cweight - $ck[3] WHERE id ='$ck[0]'";
      // Yii::$app->db->createCommand($qr)->execute();	
				
	   unset($_SESSION['JobItems'][$cid]);
	    return $this->redirect(['cinstructions']);
	}
	
	public function getSDet($orderno)
	{		 
		
		$q = "SELECT s.supcode,s.name FROM suppliers s INNER JOIN orders o ON s.supid = o.supid WHERE o.orderno = '$orderno'";
		$rs = Yii::$app->db->createCommand($q)->queryOne(0);
		
		$tbData = "<table class='table table-bordered table-gray'><thead><tr><th>SUPPLIER #</th><th>SUPPLIER NAME</th>";
		$tbData .= "<th>ORDER #</th></thead></tr></thead>";
		
		if($rs)
		{
			$tbData .="<tr><td>$rs[0]</td><td>$rs[1]</td><td>$orderno</td></tr></table>";
			return $tbData;
		}
		else
		{
			return $tbData .= "<tr><td><b>Invalid Request</td></tr></table>";
		}
		
		
	}
	
	//////////////////////////////////////////////////////////////////////////////////
	//REGISTER ITEMS
	
	public function actionAdditem() 
	{
        if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = new Items;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('isuccess',"Raw Material Item Successful added to the System.");
		  return $this->refresh();
        }
		
        return $this->render('_fitems',['model'=>$model,'tbItem'=>$this->getRItems()]);
    }
	
	public function actionEitem($id) 
	{
        if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = $this->loadItem($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('isuccess',"Raw Material Item Updated Successful.");
		  return $this->redirect(['manager/additem']);
        }
		
        return $this->render('_fitems',['model'=>$model,'tbItem'=>$this->getRItems()]);
    }
	
	
	protected function loadItem($id)
    {
        if (($model = Items::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Invalid Request.');
        }
    }
	
	public function getRItems()
	{
	    $q = "SELECT name,itemid FROM items ORDER BY name ";
		
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		$tbTr = "";
		if(!empty($rst))
		{ 
	        $i = 1;
			$tbTr .="<table class='table table-bordered table-gray'>";
		    $tbTr .="<thead><tr><th>SN</th><th>RAW MATERIAL</th><th>ACTION</th></tr></thead>";
		     foreach($rst as $rs)
			  {
			    
				$tbTr .="<tr><td>$i</td><td>$rs[0]</td>";
				$tbTr .="<td>".Html::a("<b>Edit</b>",['manager/eitem','id'=>$rs[1]])."</td></tr>";
				$i++;
			  }
			   $tbTr .="</table>";
		  }
		  
		return $tbTr;
		
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////
	///REGISTER Products
	
	public function actionAddproduct() 
	{
        if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = new Products;
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('psuccess',"Product Successful added to the System.");
		  return $this->refresh();
        }
		
        return $this->render('_fproduct',['model'=>$model,'dataProvider'=>$this->getPItems()]);
    }
	
	public function actionEproduct($pid) 
	{
        if(!FrostanRoles::isManager())
		  {
			  return $this->redirect(['operation/noaccess']);
		  }
		$model = $this->loadProduct($pid);
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
		{
		  Yii::$app->session->setFlash('psuccess',"Product Detail Updated Successful.");
		  return $this->redirect(['manager/addproduct']);
        }
		
        return $this->render('_fproduct',['model'=>$model,'dataProvider'=>$this->getPItems()]);
    }
	
	
	protected function loadProduct($id)
    {
        if (($model = Products::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Invalid Request.');
        }
    }
	
	public function getPItems()
	{
	      $q2 ="SELECT @s:=@s+1 as Sn, i.name as 'Raw Item',p.name as 'Product Name',p.code as 'Product Code',p.prodid FROM products p ";
		  $q2 .="INNER JOIN items i On i.itemid = p.itemid,(SELECT @s:=0) AS s ORDER BY p.edate DESC";
		
		$cnt = Yii::$app->db->createCommand("SELECT COUNT(*) FROM products")->queryScalar();
		$dataProvider = new SqlDataProvider([
		                    'sql'=>$q2,
							'totalCount'=>$cnt,
							'sort' => ['attributes' => ['Raw Item','Product Name','Product Code'],],
							'pagination'=>[
							               'pageSize'=>6,
	                                      ],
						    ]);

        return $dataProvider;							
		
	}
	/////////////////////////////////////////
	
	
	
	
	
	public function actionNoaccess()
	{
		return $this->render('noaccess');
	}


}
