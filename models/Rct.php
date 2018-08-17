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
class Rct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $name;
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
            [['name'], 'safe'],
        ];
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
