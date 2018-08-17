<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "orderitems".
 *
 * @property int $id
 * @property string $itemid
 * @property string $orderno
 * @property int $prodid
 * @property string $weight
 * @property string $dweight
 * @property string $price
 * @property string $ddate
 * @property string $dtime
 * @property int $locid
 * @property int $cby
 * @property string $cdate
 * @property int $dby
 * @property string $creason
 * @property int $canby
 * @property string $candate
 * @property string $status
 * @property string $chopped
 * @property string $rweight
 */
class Rorders extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $quantity;
    public static function tableName()
    {
        return 'orderitems';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['quantity', 'dweight','dwunit','locid','prodid'], 'required'],
			['quantity', 'integer','min'=>1],
			[['dweight'], 'number','min'=>0.1],
			//['quantity', 'chkQuantity'],
			//['barcode', 'chkValidity'],
        ];
    }
	
	public function chkQuantity($attribute,$params)
	 {
		  $conn = Yii::$app->db;
		  $iid = $_SESSION['iid'];
		  $weight = $_SESSION['weight'];
		  $orderno = $_SESSION['orderno'];
		
		  $qd = "SELECT COUNT(id) FROM orderitems WHERE prodid ='$iid' AND orderno ='$orderno' AND status = 'O' AND weight = '$weight'";
		   $di = Yii::$app->db->createCommand($qd)->queryScalar();
	        if($this->quantity > $di)
		    {
			  $this->addError('quantity',"Received Quantity Can not be Greater Than Ordered and Not Received Quantity");
					
			}
               
	 }
	
	public function chkValidity($attribute,$params)
	 {
	    
		  $conn = Yii::$app->db;
	      $bc = trim($this->barcode);
		  $iid = $_SESSION['iid']; 
	        if(!empty($bc))
		    {
			  $ipo = $conn->createCommand("SELECT COUNT(barcode) FROM barcodes WHERE barcode ='$bc'")->queryScalar(); 
               if($ipo == 1)
			   {
				   //Has it been used ?
			 $used = $conn->createCommand("SELECT COUNT(barcode) FROM barcodes WHERE barcode ='$bc' AND used = 'Y'")->queryScalar(); 
			        if($used > 0)
					{
						$this->addError('barcode',"This Barcode Has Already Been Used.Please Apply New Barcode to Proceed");
					}
					else
					{
					//Is it from the same product Family ?
					$qp = "SELECT COUNT(b.prodid) FROM barcodes b INNER JOIN products p ON p.prodid = b.prodid ";
                    $qp .="WHERE b.barcode = '$bc' AND p.itemid = '$iid'";
					
					$sm = $conn->createCommand($qp)->queryScalar();
					if($sm == 0)
					{
						$this->addError('barcode',"This Barcode Is of the Different Product. Please Apply Barcode for Product of this Goods");
					}
					}
			   }
               else
			   {
				   $this->addError('barcode',"Provided Barcode For This Product Is Not Correct");
			   }				   
		     
		    }
	 }
	
	public function getOrders()
	{
	  $data = [];
	  $q = "SELECT o.orderno,s.name FROM orders o INNER JOIN suppliers s ON s.supid = o.supid WHERE o.ostatus = 'O' ";
	  $q .="ORDER BY o.expddate DESC";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1].': Order # :'.$rs[0];
	  }
	  return $data;
	}
	
	public function getProducts()
	{
	  $data = [];
	   $ono = $_SESSION['orderno'];
	  $q = "SELECT DISTINCT CONCAT(o.prodid,':',p.code),p.name FROM products p INNER JOIN orderitems o ON p.prodid = o.prodid WHERE o.orderno = '$ono' ORDER BY name";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	
	public function getLocations()
	{
	  $data = [];
	  $q = "SELECT locid,name FROM locations ";
	
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	public function getQuantities()
	{
	  $data = [];
	 
	    $data[''] = 'Select';
		 if (Yii::$app->request->post('Rorders')['prodid']) 
		{
			$orderno = $_SESSION['orderno'];
			$nat = Yii::$app->request->post('Rorders')['prodid'];
			   $n = explode(":",$nat);
			   $q ="SELECT @s:=@s+1 AS id,@s:=@s AS name FROM orderitems,(SELECT @s:=0) AS id WHERE prodid ='$n[0]' AND ";
			   $q .="orderno ='$orderno' AND STATUS = 'O'";
			   $rst = Yii::$app->db->createCommand($q)->queryAll(false);
			   foreach($rst as $rs)
			   {
				   $data[$rs[0]] = $rs[0];
			   }
		}
	  return $data;
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'itemid' => 'Goods To Receive',
            'orderno' => 'Supplied Order',
            'prodid' => 'Product Name',
            'weight' => 'Weight',
            'dweight' => 'Weight Per Item',
            'price' => 'Item Price',
            'eddate' => 'Estmated Delivery Date',
            'quantity' => 'Received Quantity',
            'locid' => 'Location',
            'dwunit' => 'Unit Measure',
            'cdate' => 'Cdate',
            'dby' => 'Dby',
            'creason' => 'Creason',
            'canby' => 'Canby',
            'candate' => 'Candate',
            'status' => 'Status',
            'chopped' => 'Chopped',
            'rweight' => 'Rweight',
        ];
    }
	public function getUnit()
	{
		$data = [];
		$data['g'] = 'Gram';
		$data['kg'] = 'Kilogram';
		return $data;
	}
}
