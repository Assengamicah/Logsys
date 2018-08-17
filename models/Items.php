<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "items".
 *
 * @property int $itemid
 * @property string $name
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Items extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'items';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid','name'], 'required'],
            [['name'], 'unique'],
           // [['rate'], 'number', 'min' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'itemid' => 'Itemid',
			'gid' => 'Group Name',
            'name' => 'Item Name',
            'cper' => 'Charged Per',
            'rate' => 'Rate (In USD)',
            'eby' => 'Eby',
            'edate' => 'Edate',
        ];
    }
	
	public function getGname()
	{
		$data = [];
		$rst = Yii::$app->db->createCommand('SELECT gid,name FROM itemgroup ORDER BY name')->queryAll(false);
		foreach($rst as $rs)
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
