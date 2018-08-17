<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "chops".
 *
 * @property string $newbcode
 * @property int $prodid
 * @property int $oprodid
 * @property int $quantity
 * @property string $weight
 * @property int $cby
 * @property string $cdate
 * @property string $ctime
 * @property string $printed
 * @property int $pby
 * @property string $pdate
 * @property string $ptime
 * @property string $nweight
 * @property int $uby
 * @property string $udate
 */
class Trace extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'chops';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['barcode'], 'required'],
			['barcode','chkValidity'],
        ];
    }
	
	public function chkValidity($attribute,$params)
	 {
	    
		  $conn = Yii::$app->db;
	      $bc = trim($this->barcode);
		 
			  $q1 = "SELECT COUNT(barcode) FROM orderitems WHERE barcode ='$bc'";
			  $ipo = $conn->createCommand($q1)->queryScalar(); 
               if($ipo == 1)
			   {
				  //Mambo Mazuri
			   }
               else
			   {
				   $q2 = "SELECT COUNT(barcode) FROM chops WHERE barcode ='$bc'";
			       $ipo2 = $conn->createCommand($q2)->queryScalar(); 
				    if($ipo2 > 0)
					{
						//Mambo Mazuri
					}
					else
					{
						$this->addError('barcode',"Provided Product Barcode Is Incorrect or Have not Been Used");
					}
				   
			   }				   
		  
	 }
	 
	
    public function attributeLabels()
    {
        return [
            
            'barcode' => 'Barcode To Trace',
        ];
    }
	
	 public function getTraces()
	 {

	    $q1 = "SELECT COUNT(barcode) FROM orderitems WHERE barcode ='$this->barcode'";
		$ipo = Yii::$app->db->createCommand($q1)->queryScalar(); 
              if($ipo == 1)
			  {
	            $q = "SELECT p.name,i.batchno,o.orderno,s.name,i.status,DATE_FORMAT(o.orderdate,'%d/%m/%Y'),";
			    $q .="DATE_FORMAT(i.rdate,'%d/%m/%Y'),'IT',i.rweight FROM orderitems i INNER JOIN orders o ON o.orderno = i.orderno  ";
				$q .="INNER JOIN products p ON p.prodid = i.prodid INNER JOIN suppliers s ON s.supid = o.supid ";
			    $q .="WHERE i.barcode = '$this->barcode'";
	              
			  }
			  else
			  {
			  $q = "SELECT CONCAT(p.name,' ',c.spec),i.batchno,o.orderno,s.name,c.status,DATE_FORMAT(o.orderdate,'%d/%m/%Y'),";
			  $q .="DATE_FORMAT(i.rdate,'%d/%m/%Y'),'CP',SUM(c.weight) FROM chops c INNER JOIN orderitems i ON ";
              $q .="i.id = c.oprodid INNER JOIN orders o ON	o.orderno = i.orderno INNER JOIN products p ON p.prodid = c.prodid ";
			  $q .="INNER JOIN suppliers s ON s.supid = o.supid WHERE c.barcode = '$this->barcode' GROUP BY ";
			  $q .="p.name,i.batchno,o.orderno,s.name";
	             
			  }
			  $rs = Yii::$app->db->createCommand($q)->queryOne(0);
			  
			  $tbData = "<table class='table table-bordered table-gray'>";
			  $tbData .="<tr><td colspan=2><div class='alert alert-warning'><b>Tracing Results</b></div></td></tr>";
			  $tbData .="<tr><td width=20%>&nbsp;<b>Product Name</b></td><td>&nbsp;$rs[0]</td></tr>";
			  $tbData .="<tr><td>&nbsp;<b>Weight</b></td><td>&nbsp;$rs[8] KG</td></tr>";
			  $tbData .="<tr><td>&nbsp;<b>Supplier</b></td><td>&nbsp;$rs[3]</td></tr>";
			  $tbData .="<tr><td>&nbsp;<b>Order Number</b></td><td>&nbsp;$rs[2]</td></tr>";
			  $tbData .="<tr><td>&nbsp;<b>Order Date</b></td><td>&nbsp;$rs[5]</td></tr>";
			  $tbData .="<tr><td>&nbsp;<b>Batch Number</b></td><td>&nbsp;$rs[1]</td></tr>";
			  $tbData .="<tr><td>&nbsp;<b>Received Date</b></td><td>&nbsp;$rs[6]</td></tr>";
			  if(($rs[4] == 'A') || ($rs[4] == 'R'))
			  {
				$ql = "SELECT l.name FROM locations l INNER JOIN orderitems i ON l.locid = i.locid WHERE i.barcode = '$this->barcode'";
				 if($rs[7] == 'CP')
				{
					$ql = "SELECT l.name FROM locations l INNER JOIN orderitems i ON l.locid = i.locid INNER JOIN chops c ON ";
					$ql .="i.id = c.oprodid WHERE c.barcode = '$this->barcode' LIMIT 1";
				}
				$loc = Yii::$app->db->createCommand($ql)->queryScalar();
				$tbData .="<tr><td>&nbsp;<b>Status</b></td><td>&nbsp;Available In Stock</td></tr>";  
				$tbData .="<tr><td>&nbsp;<b>Location</b></td><td>&nbsp;$loc</td></tr>";  
			  }
			  else
			  {
				$qd = "SELECT CONCAT(e.fname,' ',e.mname,' ',e.sname),DATE_FORMAT(i.sdate,'%d/%m/%Y'),i.stime FROM orderitems i ";
				$qd .= "INNER JOIN employees e ON e.empid = i.sby WHERE i.barcode = '$this->barcode'";
				if($rs[7] == 'CP')
				{
				  $qd = "SELECT CONCAT(e.fname,' ',e.mname,' ',e.sname),DATE_FORMAT(c.sdate,'%d/%m/%Y'),c.stime FROM chops c ";
				  $qd .= "INNER JOIN employees e ON e.empid = c.sby WHERE c.barcode = '$this->barcode'";	
				}
				$d = Yii::$app->db->createCommand($qd)->queryOne(0);
				$tbData .="<tr><td>&nbsp;<b>Status</b></td><td>&nbsp;Cleared</td></tr>";  
                $tbData .="<tr><td>&nbsp;<b>Cleared By</b></td><td>&nbsp;$d[0]</td></tr>";  
                $tbData .="<tr><td>&nbsp;<b>Cleared Date</b></td><td>&nbsp;$d[1]</td></tr>";  
                $tbData .="<tr><td>&nbsp;<b>Cleared Time</b></td><td>&nbsp;$d[2]</td></tr>";  				
			  }
			   $tbData .="</table>";
			  return $tbData;
	   
			
	 }
}
