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
class Barcodes extends \yii\db\ActiveRecord
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
            [['prodid','quantity'], 'required'],
			[['quantity'], 'integer','min'=>1],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
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
