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
class Chops extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $orderno;
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
            [['orderno'], 'required'],
        ];
    }
	
	public function getCOrders()
	{
	  $data = [];
	 
	  $q ="SELECT  DISTINCT o.orderno,s.name FROM orders o INNER JOIN suppliers s ON s.supid = o.supid INNER JOIN orderitems ot "; 
	  $q .="ON o.orderno = ot.orderno WHERE ot.status = 'R' AND ot.chopped = 'N' ORDER BY o.expddate DESC";
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
            'newbcode' => 'Newbcode',
            'orderno' => 'Supplied Order',
            'oprodid' => 'Supplied Product to be Cut',
            'quantity' => 'Quantity',
            'weight' => 'Weight',
            'cby' => 'Cby',
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
