<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "supcontacts".
 *
 * @property int $id
 * @property int $supid
 * @property string $fullname
 * @property string $title
 * @property string $phone
 * @property string $email
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Supcontacts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'supcontacts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fullname', 'title','phone'], 'required'],
            ['email', 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supid' => 'Supid',
            'fullname' => 'Contact Person Fullname',
            'title' => 'Contact Person Title',
            'phone' => 'Contact Person Phone',
            'email' => 'Contact Person Email',
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
		  $this->supid = $_SESSION['supid'];
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
