<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "itemgroup".
 *
 * @property int $gid
 * @property string $name
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Itemgroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'itemgroup';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','cper','rate'], 'required'],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'gid' => 'Gid',
            'name' => 'Shipping Item Group Name',
			'cper' => 'Charged Per',
            'rate' => 'Rate (In USD)',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
            'eby' => 'Eby',
            'edate' => 'Edate',
        ];
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
