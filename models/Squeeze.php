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
class Squeeze extends \yii\db\ActiveRecord
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
			[['iid'], 'chkType'],
        ];
    }
	
	public function chkType($attribute,$params)
	 {
		   if(!empty($_SESSION['JobItems']))
		   {
		    $gid = Yii::$app->db->createCommand("SELECT gid FROM orderitems WHERE id = '$this->iid'")->queryScalar();   
		    $conn = Yii::$app->db;
			 foreach($_SESSION['JobItems'] as $ckey=>$val)
			 {
		        $gid2 = Yii::$app->db->createCommand("SELECT gid FROM orderitems WHERE id = '$ckey'")->queryScalar(); 
				if($gid != $gid2)
				{
				 $this->addError('iid',"You Can Not Squeeze or Repack Items Of The Different Type");
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
