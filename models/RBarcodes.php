<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "barcodes".
 *
 * @property int $id
 * @property int $prodid
 * @property string $barcode
 * @property string $used
 */
class RBarcodes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $quantity;
    public static function tableName()
    {
        return 'barcodes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prodid','refid','quantity'], 'required'],
			[['quantity'], 'integer','min'=>1],
			['refid', 'chkRef'],
			['prodid', 'chkProd'],
            
        ];
    }

    /**
     * @inheritdoc
     */
	 public function chkRef($attribute,$params)
	 {
	    
		  $conn = Yii::$app->db;
	      $refid = trim($this->refid);

	        if(!empty($refid))
		    {
			  $ipo = $conn->createCommand("SELECT COUNT(id) FROM barcodes WHERE refid ='$refid'")->queryScalar(); 
               if($ipo > 0)
			   {
				   //Has it been used ?
			   }
               else
			   {
				   $this->addError('refid',"Order Number Provided Either Does not Exist Or No Product From this Order has been Received");
			   }				   
		     
		    }
		 
	 }
	 
	  public function chkProd($attribute,$params)
	 {
	    
		  $conn = Yii::$app->db;
	      $refid = trim($this->refid);

	        if(!empty($refid))
		    {
	$ipo = $conn->createCommand("SELECT COUNT(id) FROM barcodes WHERE refid ='$refid' AND prodid = '$this->prodid'")->queryScalar(); 
               if($ipo > 0)
			   {
				   
			   }
               else
			   {
				   $this->addError('prodid',"This Product is not from this Order. Please select Product that are from this order to print barcode.");
			   }				   
		     
		    }
		 
	 }
    public function attributeLabels()
    {
        return [
            'refid' => 'Barcode For Order Number',
            'prodid' => 'Product',
            'quantity' => 'Number Of Barcode To Print',
            'used' => 'Used',
        ];
    }
	
	public function getProducts()
	{
	  $data = [];
	  $q = "SELECT prodid,name FROM products ORDER BY name";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
}
