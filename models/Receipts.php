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
class Receipts extends \yii\db\ActiveRecord
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
            [['iid'], 'required'],
        ];
    }
	
	public function chkCBM($attribute,$params)
	 {
		   $iid = $this->iid;
		   if(!empty($iid))
		   {
		     $exp = explode(":",$iid);
		   
	        if($exp[1] == 'CBM')
		    {
		      if(empty($this->cbm))
			  {
			  $this->addError('cbm',"Total CBM is Required");
			  }
		    }
			
		  }
	 }
	
	
	public function chkCno($attribute,$params)
	 {
		   $cno = trim($this->cno);
		   if(!empty($cno))
		   {
		   $conn = Yii::$app->db;
		   $rs = $conn->createCommand("SELECT COUNT(*) FROM orderitems WHERE cno ='$cno'")->queryScalar(); 
	        if($rs > 0)
		    {
		     $this->addError('cno',"This Control Number Has already been used");
		    }
			else
			{
				$rs = $conn->createCommand("SELECT COUNT(*) FROM osqueezed WHERE cno ='$cno'")->queryScalar(); 
	            if($rs > 0)
		        {
		          $this->addError('cno',"This Control Number Has already been used");
		        }
				else
				{
					$rs = $conn->createCommand("SELECT COUNT(*) FROM osqueezed WHERE ncno ='$cno'")->queryScalar(); 
	                 if($rs > 0)
		             {
		              $this->addError('cno',"This Control Number Has already been used");
		             }
				}
			}
		   }
	 }
	 
	
	public function getItems()
	{
	  $data = [];
	  $rst = Yii::$app->db->createCommand("SELECT CONCAT(iid,':',cper),CONCAT(name,' - ',cper) FROM items WHERE inext = 'E' ORDER BY name")->queryAll(false);
	  foreach($rst as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	
	public function getItemsP()
	{
	  $data = [];
	  $rslt = Yii::$app->db->createCommand("SELECT CONCAT(itemid,':',prodid),name FROM products ORDER BY name")->queryAll(0);
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
            'iid' => 'Item Name',
            'orderno' => 'Orderno',
            'cbm' => 'CBM',
            'weight' => 'Weight(KG)',
            'dweight' => 'Dweight',
            'price' => 'Item Price',
            'eddate' => 'Estmated Delivery Date',
            'quantity' => 'Quantity',
            'locid' => 'Locid',
            'cno' => 'Control Number',
            'cdate' => 'Cdate',
            'dby' => 'Dby',
            'creason' => 'Creason',
            'canby' => 'Canby',
            'candate' => 'Candate',
            'status' => 'Status',
            'chopped' => 'Chopped',
            'munit' => 'Unit Measure',
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
