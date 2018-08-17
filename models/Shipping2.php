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
class Shipping2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $picked;
	 public $it;
    public static function tableName()
    {
        return 'orderitems';
    }

    /**
     * @inheritdoc
     */
	 
    public function rules()
    {
        return [
            [['picked','id'], 'required'],
			
        ];
    }
	
	public function chkDate($attribute,$params)
	 {
	   
		  $sdates = explode("/",$this->expsdate);
		  $edates = explode("/",$this->expardate);
		  
	      $sdate = $sdates[2]."-".$sdates[1]."-".$sdates[0];
		  $edate = $edates[2]."-".$edates[1]."-".$edates[0];
		  
		  $conn = Yii::$app->db;
	      
		   $rslt = $conn->createCommand("SELECT DATEDIFF('$edate','$sdate')")->queryScalar(); 
	        if($rslt < 0)
		    {
		     $this->addError('expsdate',"Expected Shipping Date Can Not Be Greater Than Expected Shipping Arrival Date");
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
		     $this->addError('expsdate',"Supplier Registration Date can not be greater than today's Date");
		    }
	 }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orderno' => 'Orderno',
            'containerno' => 'Container Number',
            'id' => 'Item',
            'expsdate' => 'Expected Shipping Date',
            'expardate' => 'Expected Arrival Date',
            'ostatus' => 'Ostatus',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
            'eby' => 'Eby',
            'edate' => 'Edate',
            'creasen' => 'Creasen',
            'canby' => 'Canby',
            'candate' => 'Candate',
        ];
    }
	
	public function getSLine()
	{
	  $data = [];
	  $rslt = Yii::$app->db->createCommand("SELECT slid,name FROM sline ORDER BY name")->queryAll(false);

	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
}
