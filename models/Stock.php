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
class Stock extends \yii\db\ActiveRecord
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
            [['orderno', 'prodid', 'locid'], 'required'],
        ];
    }
	
	
	public function chkDate($attribute,$params)
	 {
	   
		  $pdates = explode("/",$_SESSION['ddate']);
	      $ddates = explode("/",$this->eddate);
		  
	      $pdate = $pdates[2]."-".$pdates[1]."-".$pdates[0];
		  $ddate = $ddates[2]."-".$ddates[1]."-".$ddates[0];
		  
		  $conn = Yii::$app->db;
	      
		   $rslt = $conn->createCommand("SELECT DATEDIFF('$ddate','$pdate')")->queryScalar(); 
	        if($rslt < 0)
		    {
		     $this->addError('eddate',"Batch Delivered Date Can Not Less than Order Delivery Date");
		    }
	 }
	 
	
	public function getOrders()
	{
	  $data = [];
	  $q = "SELECT DISTINCT o.orderno,s.name FROM orders o INNER JOIN suppliers s ON s.supid = o.supid "; 
      $q .="INNER JOIN orderitems ot ON o.orderno = ot.orderno WHERE ot.status ='R' AND rweight > 0  ORDER BY s.name";
	  
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = 'Order No: '.$rs[0].' : '.$rs[1];
	  }
	  return $data;
	}
	
	public function getProducts()
	{
	  $data = [];
	 
	   if (Yii::$app->request->post('Stock')['orderno']) 
		{
			$orderno = Yii::$app->request->post('Stock')['orderno'];
			  $q = "SELECT o.id,CONCAT(o.barcode,':',p.name,' : ',l.name) as name FROM products p INNER JOIN orderitems o ON ";
			   $q .="p.prodid = o.prodid  INNER JOIN locations l ON l.locid = o.locid ";
			   $q .="WHERE o.orderno = '$orderno' AND o.status = 'R' AND o.rweight > 0";
			    $rst = Yii::$app->db->createCommand($q)->queryAll(false);
            foreach ($rst as $rs)
			{
                $data[$rs[0]] = $rs[1];
            }
        }
	  
	  return $data;
	}
	
	public function getLocations()
	{
	  $data = [];
	  $rslt = Yii::$app->db->createCommand("SELECT locid,name FROM locations ORDER BY name")->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
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
            'itemid' => 'Item Name',
            'orderno' => 'Order',
            'prodid' => 'Product',
            'weight' => 'Weight(KG)',
            'dweight' => 'Dweight',
            'price' => 'Item Price',
            'eddate' => 'Estmated Delivery Date',
            'quantity' => 'Quantity',
            'locid' => 'New Location',
            'cby' => 'Cby',
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
}
