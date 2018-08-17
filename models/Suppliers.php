<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "suppliers".
 *
 * @property int $supid
 * @property string $supcode
 * @property string $name
 * @property string $address
 * @property string $paddress
 * @property string $suptype
 * @property string $phone
 * @property string $email
 * @property int $cid
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Suppliers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'suppliers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supcode', 'name', 'suptype','paddress','phone','cid','regdate','status'], 'required'],
			[[ 'supcode','name'], 'unique'],
			[ 'email', 'email'],
			['regdate', 'date', 'format' => 'php:d/m/Y'],
			['regdate','chkDate'],
            [['address','website'], 'safe'],
           
        ];
    }
	
	
	public function chkDate($attribute,$params)
	 {
	   
		  $todate = date("Y-m-d");
	      $edates = explode("/",$this->regdate);
	      $rdate = $edates[2]."-".$edates[1]."-".$edates[0];
		  $conn = Yii::$app->db;
	      
		   $rslt = $conn->createCommand("SELECT DATEDIFF('$todate','$rdate')")->queryScalar(); 
	        if($rslt < 0)
		    {
		     $this->addError('regdate',"Supplier Registration Date can not be greater than today's Date");
		    }
	 }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'supid' => 'Supid',
            'supcode' => 'Supplier Registration #',
            'name' => 'Supplier Name',
            'address' => 'Address',
            'paddress' => 'Physical address',
            'suptype' => 'Supplier Type',
            'phone' => 'Phone',
            'email' => 'Email',
            'cid' => 'Country',
            'regdate' => 'Supplier Registration Date',
            'cdate' => 'Cdate',
            'status' => 'Supplier Status',
            'website' => 'Supplier Website',
        ];
    }
	
	public function getCountries()
	{
	  $data = [];
	  $dt = Yii::$app->db->createCommand("SELECT cid,name FROM countries WHERE cid = 185")->queryOne(0);
	  $rslt = Yii::$app->db->createCommand("SELECT cid,name FROM countries WHERE cid != 185 ORDER BY name")->queryAll(0);
	  
	  $data[$dt[0]] = $dt[1];
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	
	public function getStype()
	{
	  $data = [];
	  
	    $data['INDIVIDUAL'] = 'INDIVIDUAL';
		$data['COMPANY'] = 'COMPANY';
	  
	  return $data;
	}
	
	public function getStatus()
	{
	  $data = [];
	  
	    $data['A'] = 'ACTIVE';
		$data['S'] = 'SUSPENDED';
	  
	  return $data;
	}
	
	 public function beforeSave($insert)
	 {
	   if(parent::beforeSave($insert))  //call parent method so that the events are fired appropriately
	   {
		    $edate = explode("/",$this->regdate);
			$this->regdate = $edate[2].'-'.$edate[1].'-'.$edate[0];
			
			
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
