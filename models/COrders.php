<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "orders".
 *
 * @property string $orderno
 * @property string $batchno
 * @property int $supid
 * @property string $orderdate
 * @property string $exppdate
 * @property string $expddate
 * @property string $ostatus
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 * @property string $creasen
 * @property int $canby
 * @property string $candate
 */
class COrders extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['creason'], 'required'],
			
        ];
    }
	
	public function chkDate($attribute,$params)
	 {
	   
		  $pdates = explode("/",$this->exppdate);
	      $ddates = explode("/",$this->expddate);
		  
	      $pdate = $pdates[2]."-".$pdates[1]."-".$pdates[0];
		  $ddate = $ddates[2]."-".$ddates[1]."-".$ddates[0];
		  
		  $conn = Yii::$app->db;
	      
		   $rslt = $conn->createCommand("SELECT DATEDIFF('$ddate','$pdate')")->queryScalar(); 
	        if($rslt < 0)
		    {
		     $this->addError('exppdate',"Estimated Production Date Can Not be greater than Estimated Delivered Date");
		    }
	 }
	 
	 public function chkDate2($attribute,$params)
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
            'orderno' => 'Orderno',
            'batchno' => 'Batchno',
            'supid' => 'Supplier',
            'orderdate' => 'Orderdate',
            'exppdate' => 'Estimated Production Date',
            'expddate' => 'Estimated Delivery Date',
            'ostatus' => 'Ostatus',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
            'eby' => 'Eby',
            'edate' => 'Edate',
            'creason' => 'Please Provide Reason Why you Want to Cancel This Addendum',
            'canby' => 'Canby',
            'candate' => 'Candate',
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
			$this->ostatus = 'C';
			$this->canby = Yii::$app->user->id;
			$this->candate = new \yii\db\Expression('NOW()');
		 }
		 return true;
	   }
	   return false;
	 }
}
