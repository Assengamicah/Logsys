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
class RO extends \yii\db\ActiveRecord
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
            [['orderno','batchno'], 'required'],
        ];
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
	public function getItems()
	{
	  $data = [];
	 
	    $data[$rs['']] = 'Select';
	  
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
            'prodid' => 'Prodid',
            'weight' => 'Weight',
            'dweight' => 'Dweight',
            'price' => 'Item Price',
            'eddate' => 'Estmated Delivery Date',
            'quantity' => 'Quantity',
            'locid' => 'Locid',
            'batchno' => 'Batch No',
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
