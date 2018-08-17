<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "chops".
 *
 * @property string $newbcode
 * @property int $prodid
 * @property int $oprodid
 * @property int $quantity
 * @property string $weight
 * @property int $cby
 * @property string $cdate
 * @property string $ctime
 * @property string $printed
 * @property int $pby
 * @property string $pdate
 * @property string $ptime
 * @property string $nweight
 * @property int $uby
 * @property string $udate
 */
class Sales extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'chops';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['barcode'], 'required'],
			['barcode','chkValidity'],
        ];
    }
	
	public function chkValidity($attribute,$params)
	 {
	    
		  $conn = Yii::$app->db;
	      $bc = trim($this->barcode);
		 
			  $q1 = "SELECT COUNT(barcode) FROM orderitems WHERE barcode ='$bc' AND status ='R' AND rweight > 0";
			  $ipo = $conn->createCommand($q1)->queryScalar(); 
               if($ipo == 1)
			   {
				  //Mambo Mazuri
			   }
               else
			   {
				   $q2 = "SELECT COUNT(barcode) FROM chops WHERE barcode ='$bc' AND status ='A'";
			       $ipo2 = $conn->createCommand($q2)->queryScalar(); 
				    if($ipo2 > 0)
					{
						//Mambo Mazuri
					}
					else
					{
						$this->addError('barcode',"Provided Product Barcode Is not Correct Or The Product Has Alredy Been Delivered");
					}
				   
			   }				   
		  
	 }
	 
	
    public function attributeLabels()
    {
        return [
            
            'barcode' => 'Proceed To Dispatch Product By Scanning Its Barcode',
        ];
    }
	
	 public function recordSales()
	 {
	   $from = 'item';
	   $id = Yii::$app->user->id;
	    $q1 = "SELECT COUNT(barcode) FROM orderitems WHERE barcode ='$this->barcode' AND status ='R' AND rweight > 0";
		$ipo = Yii::$app->db->createCommand($q1)->queryScalar(); 
              if($ipo == 1)
			  {
	             $q = "UPDATE orderitems SET status ='S',sby ='$id',sdate = CURDATE(),stime = CURTIME() ";
	             $q .="WHERE barcode = '$this->barcode'";
	              Yii::$app->db->createCommand($q)->execute();
			  }
			  else
			  {
				  $from = 'chops';
				  $q = "UPDATE chops SET status ='S',sby ='$id',sdate = CURDATE(),stime = CURTIME() ";
	              $q .="WHERE barcode = '$this->barcode'";
	              Yii::$app->db->createCommand($q)->execute();
			  }
			  return $from;
	   
			
	 }
}
