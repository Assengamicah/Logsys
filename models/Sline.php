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
class Sline extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sline';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','paddress','phone','email'], 'required'],
            [['name','email'], 'unique'],
			[['email'], 'email'],
            [['address'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
           'sid' => 'Sid',
			'name' => 'Name',
			'paddress' => 'Physical Address',
			'address' => 'Postal Address',
			'phone' => 'Phone',
			'email' => 'Email',
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
