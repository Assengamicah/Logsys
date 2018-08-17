<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "clients".
 *
 * @property int $cid
 * @property string $name
 * @property string $address
 * @property string $paddress
 * @property string $ctype
 * @property string $phone
 * @property string $phone2
 * @property string $email
 * @property string $pcode
 * @property string $pcode2
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Clients extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clients';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pcode', 'name','ctype','phone','ctype'], 'required'],
			[['name'],'unique'],
            [['email'], 'email'],
            [['phone','phone2'], 'integer'],
            ['phone', 'chkPhone1'],
            ['phone2', 'chkPhone2'],
            [['email', 'pcode2','address','paddress','phone2'], 'safe'],
        ];
    }
	
	public function chkPhone1($attribute,$params)
	 {
	    if(is_numeric($this->phone))
		  {
		   $fchar = substr($this->phone,0,1);
			if($fchar == '0')
			 {
			  $this->addError('phone','Invalid Phone Number.All Number should not start with Zero and Length of Number must be Nine(9).');
			 }
			if(strlen($this->phone) !== 9)
			{
				 $this->addError('phone','Invalid Phone Number.All Number should not start with Zero and Length of Number must be Nine(9).');
			}
		  }
		  else
		  {
			  $this->addError('phone','Invalid Phone Number.All Number should not start with Zero and Length of Number must be Nine(9).');
		  }
	 } 
	 
	 public function chkPhone2($attribute,$params)
	 {
       if(!empty($this->phone2))
	   {		   
	   if(is_numeric($this->phone2))
		  {
		   $fchar = substr($this->phone2,0,1);
			if($fchar == '0')
			 {
			  $this->addError('phone2','Invalid Phone Number.All Number should not start with Zero and Length of Number must be Nine(9).');
			 }
			if(strlen($this->phone2) !== 9)
			{
				 $this->addError('phone2','Invalid Phone Number.All Number should not start with Zero and Length of Number must be Nine(9).');
			}
		  }
		  else
		  {
			  $this->addError('phone2','Invalid Phone Number.All Number should not start with Zero and Length of Number must be Nine(9).');
		  }
		  
		  if(empty($this->pcode2))
		  {
			   $this->addError('pcode2','Country code is required.');
		  }
	   }
	 } 

    /**
     * @inheritdoc
     */
	 
    public function attributeLabels()
    {
        return [
            'cid' => 'Cid',
            'name' => 'Client Name',
            'address' => 'Postal Address',
            'paddress' => 'Physical Address',
            'ctype' => 'Client Type',
            'phone' => 'Primary Phone Number',
            'phone2' => 'Alternative Phone Number',
            'email' => 'Email',
            'pcode' => 'Country Code',
            'pcode2' => 'Country Code',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
            'eby' => 'Eby',
            'edate' => 'Edate',
        ];
    }
	
	public function getPcode()
	{
	  $data = [];
	  $rst = Yii::$app->db->createCommand("SELECT phonecode,CONCAT(iso3,' - ',phonecode) FROM countries")->queryAll(false);
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
