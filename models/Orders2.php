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
class Orders2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $sas;
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
            [['cid','orderdate'], 'required'],
			[['orderdate'], 'date', 'format' => 'php:d/m/Y'],
			['orderdate','chkDate'],
			['sas','safe'],
        ];
    }
	
	public function chkDate($attribute,$params)
	 {
	   
		  $odates = explode("/",$this->orderdate);
	      $odate = $odates[2]."-".$odates[1]."-".$odates[0];
		  
		  
		  $conn = Yii::$app->db;
	      
		   $rslt = $conn->createCommand("SELECT DATEDIFF(CURDATE(),'$odate')")->queryScalar(); 
	        if($rslt < 0)
		    {
		     $this->addError('orderdate',"Cargo Registration Date Can Not Be Greater Than Today's Date");
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
	 
	 public function getBSas()
	{
	  $data = [];
	   if (Yii::$app->request->post('Orders')['cid']) 
		{
			$pid = Yii::$app->request->post('Orders')['cid'];
	        $rst = Yii::$app->db->createCommand("SELECT sas,sas FROM sas WHERE cid ='$pid' ORDER BY sas")->queryAll(false);
			  foreach($rst as $rs)
			  {
				$data[$rs[0]] = $rs[1];
			  }
		}
	 return $data;
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orderno' => 'Orderno',
            'batchno' => 'Batchno',
            'cid' => 'Customer',
            'orderdate' => 'Receiving Date',
            'exppdate' => 'Estimated Production Date',
            'expddate' => 'Estimated Delivery Date',
            'ostatus' => 'Ostatus',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
            'eby' => 'Eby',
            'edate' => 'Edate',
            'creasen' => 'Creasen',
            'canby' => 'Canby',
            'sas' => 'Shipping As',
        ];
    }
	
	public function getClients()
	{
	  $data = [];
	  $rslt = Yii::$app->db->createCommand("SELECT cid,name FROM clients ORDER BY name")->queryAll(false);

	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	
	 public function getSas()
	{
	  $data = [];
	   if (Yii::$app->request->post('Orders')['cid'])
		{
			$pid = Yii::$app->request->post('Orders')['cid'];
	        $rst = Yii::$app->db->createCommand("SELECT sas,sas FROM sas WHERE cid ='$pid' ORDER BY sas")->queryAll(false);
			  foreach($rst as $rs)
			  {
				$data[$rs[0]] = $rs[1];
			  }
		}
	 return $data;
	}
}
