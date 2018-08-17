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
class Chops2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $supid;
	 public $quantity;
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
            [['spec','oprodid','prodid','quantity'], 'required'],
			['quantity','integer','min'=>1],
			['quantity', 'chkQuantity'],
			//['weight','chkTotal'],
        ];
    }
	
	public function chkQuantity($attribute,$params)
	 {
		  $conn = Yii::$app->db;
		  $pid = $this->oprodid;
		  $exp = explode(":",$pid);
		  
		  $orderno = $_SESSION['orderno'];
		
	 $qd = "SELECT COUNT(id) FROM orderitems WHERE prodid ='$exp[0]' AND orderno ='$orderno' AND status = 'R' AND rweight = '$exp[1]'";
		   $di = Yii::$app->db->createCommand($qd)->queryScalar();
		  
	        if($this->quantity > $di)
		    {
			  $this->addError('quantity',"Quantity To Cut Can Not Exceed Received And Not Already Cut Quantity");
					
			}
               
	 }
	
	
	 public function chkTotal($attribute,$params)
	 {
	        $conn = Yii::$app->db;
		    $wgt = $conn->createCommand("SELECT rweight,cweight FROM orderitems WHERE id ='$this->oprodid'")->queryOne(0);
			if($wgt[0] < ($wgt[1] + $this->weight))
			{
		    
			   $this->addError('weight','Total CUT Weights Can Not Exceed Product Weight');
			 
			}
	 } 
	
	public function getCProducts()
	{
	  $data = [];
	  $nat = $_SESSION['orderno'];
	 // $q = "SELECT o.id,p.name,o.barcode,o.rweight FROM products p INNER JOIN orderitems o ON p.prodid = o.prodid  WHERE ";
	 // $q .="o.orderno = '$nat' AND o.chopped = 'N' AND o.status = 'R'";
	 
	  $q = "SELECT o.prodid,p.name,o.rweight,o.rwunit,COUNT(o.id) FROM products p INNER JOIN orderitems o ON p.prodid = o.prodid ";
	  $q .="WHERE o.orderno = '$nat' AND o.chopped = 'N' AND o.status = 'R' GROUP BY o.prodid,p.name,o.rweight ";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0].':'.$rs[2]] = $rs[1].' ('.$rs[2].' '.$rs[3]. ') : Avail. '.$rs[4];
	  }
	  return $data;
	}
	
	public function getCProducts2()
	{
	  $data = [];
	  $nat = $_SESSION['orderno'];
	 // $q = "SELECT o.id,p.name,o.barcode,o.rweight FROM products p INNER JOIN orderitems o ON p.prodid = o.prodid  WHERE ";
	 // $q .="o.orderno = '$nat' AND o.chopped = 'N' AND o.status = 'R'";
	 
	  $q = "SELECT o.prodid,p.name,o.rweight,o.rwunit,COUNT(o.id) FROM products p INNER JOIN orderitems o ON p.prodid = o.prodid ";
	  $q .="WHERE o.orderno = '$nat' AND o.status = 'R' AND o.rweight > 0 GROUP BY o.prodid,p.name,o.rweight ";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0].':'.$rs[2]] = $rs[1].' ('.$rs[2].' '.$rs[3]. ') : Avail. '.$rs[4];
	  }
	  return $data;
	}
	
	public function getItems()
	{
	  $data = [];
	 
	   if (Yii::$app->request->post('Chops2')['oprodid']) 
		{
			$nat = $_SESSION['orderno'];
			$pid = Yii::$app->request->post('Chops2')['oprodid'];
			$exp = explode(":",$pid);
			   $q = "SELECT itemid,prodid FROM orderitems WHERE orderno = '$nat' AND prodid = '$exp[0]' AND rweight = '$exp[1]' LIMIT 1";
			   $iid = Yii::$app->db->createCommand($q)->queryOne(0);
			   
			   $q = "SELECT prodid as id,name FROM products  WHERE itemid ='$iid[0]' AND prodid != '$iid[1]'";
            $rst = Yii::$app->db->createCommand($q)->queryAll(false);
            foreach ($rst as $rs)
			{
                $data[$rs[0]] = $rs[1];
            }
        }
	  
	  return $data;
	}
	
	public function getSpecs()
	{
	  $data = [];
	 
	    $data[''] = 'Select';
		$data['BONELESS'] = 'BONELESS';
		$data['BONE IN'] = 'BONE IN';
	  
	  return $data;
	}


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prodid' => 'CUTS',
            'supid' => 'Supplier',
            'oprodid' => 'PRODUCT',
            'quantity' => 'Quantity',
            'weight' => 'WEIGHT',
            'spec' => 'SPEC',
            'cdate' => 'Cdate',
            'ctime' => 'Ctime',
            'printed' => 'Printed',
            'pby' => 'Pby',
            'pdate' => 'Pdate',
            'ptime' => 'Ptime',
            'nweight' => 'Nweight',
            'uby' => 'Uby',
            'udate' => 'Udate',
        ];
    }
}
