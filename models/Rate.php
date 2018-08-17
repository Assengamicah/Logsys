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
class Rate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'exchangerate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['erate'], 'required'],
            [['erate'], 'number', 'min' => 2000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'erate' => 'New Rate',
			'fordate' => 'Fordate',
        ];
    }
	
	public function beforeSave($insert)
	 {
	   if(parent::beforeSave($insert))  //call parent method so that the events are fired appropriately
	   {
		   
		 if($this->isNewRecord)
		 {
			$this->status = 'C';
			$this->cby = Yii::$app->user->id;
			$this->fordate = new \yii\db\Expression('CURDATE()');
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
