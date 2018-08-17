<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property int $prodid
 * @property int $itemid
 * @property string $name
 * @property string $code
 * @property string $descr
 * @property string $picture
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Products extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['itemid', 'name', 'code'], 'required'],
            ['code', 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prodid' => 'Prodid',
            'itemid' => 'Raw Material',
            'name' => 'Product Name',
            'code' => 'Product Code',
            'descr' => 'Descr',
            'picture' => 'Picture',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
            'eby' => 'Eby',
            'edate' => 'Edate',
        ];
    }
	
	public function getItems()
	{
	  $data = [];
	 
	  $q ="SELECT  itemid,name FROM items ORDER BY name ";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	
	public function beforeSave($insert)
	 {
	   if(parent::beforeSave($insert))  //call parent method so that the events are fired appropriately
	   {
		   
		 if($this->isNewRecord)
		 {
			$this->cby = Yii::$app->user->id;
			$this->cdate = new \yii\db\Expression('NOW()');
			$this->eby = Yii::$app->user->id;
			$this->edate = new \yii\db\Expression('NOW()');
		 }
		 else
		 {
			$this->eby = Yii::$app->user->id;
			$this->edate = new \yii\db\Expression('NOW()');
		 }
		 return true;
	   }
	   return false;
	 }
}
