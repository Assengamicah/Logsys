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
class Barcodes2 extends \yii\db\ActiveRecord
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
            [['refid'], 'required'],
			['refid', 'chkRef'],
			
            
        ];
    }

    
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
			 $used = $conn->createCommand("SELECT COUNT(barcode) FROM barcodes WHERE refid ='$refid' AND printed = 'N'")->queryScalar(); 
			        if($used == 0)
					{
						$this->addError('refid',"All Barcode From This Order Has Already Been Used.Use Re-print Barcode to get Product Barcode from this Order that has not been Sold.");
					}
			   }
               else
			   {
				   $this->addError('refid',"Order Number Provided Either Does not Exist Or No Product From this Order has been Received");
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
	  $q = "SELECT CONCAT(prodid,':',code),name FROM products ORDER BY name";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
}
