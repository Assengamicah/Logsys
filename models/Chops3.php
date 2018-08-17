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
class Chops3 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $supid;
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
            [['cicode'], 'required'],
			['cicode','isValid'],
        ];
    }
	
	
	 public function isValid($attribute,$params)
	 {
	        $conn = Yii::$app->db;
			$this->cicode = trim($this->cicode);
		 $cnt = $conn->createCommand("SELECT COUNT(*) FROM cinstruction WHERE cicode ='$this->cicode' AND printed = 'Y'")->queryScalar();
			if($cnt > 0)
			{
		   $cnt2 = $conn->createCommand("SELECT COUNT(*) FROM cinstruction WHERE cicode ='$this->cicode' AND isdone = 'Y'")->queryScalar();
			   if($cnt2 > 0)
			   {
			    $this->addError('instrcode','You can not Update Portions because It has already been Updated.');
			   } 
			 
			}
			else
			{
				$this->addError('instrcode','Cuting Preparation Form Document Number Provided is Invalid/Not Printed');
			}
	 } 
	
	public function getCProducts()
	{
	  $data = [];
	  $nat = $_SESSION['orderno'];
	  $q = "SELECT o.id,p.name,o.barcode,o.rweight FROM products p INNER JOIN orderitems o ON p.prodid = o.prodid  WHERE ";
	  $q .="o.orderno = '$nat' AND o.chopped = 'N' AND o.status = 'R'";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1].' ('.$rs[3].'KG) : Barcode # '.$rs[2];
	  }
	  return $data;
	}
	
	public function getItems()
	{
	  $data = [];
	 
	   if (Yii::$app->request->post('Chops2')['oprodid']) 
		{
			$pid = Yii::$app->request->post('Chops2')['oprodid'];
			   $q = "SELECT itemid,prodid FROM orderitems WHERE id = '$pid'";
			   $iid = Yii::$app->db->createCommand($q)->queryOne(0);
			   
			   $q = "SELECT prodid as id,name FROM products  WHERE itemid ='$iid[0]' AND prodid != '$iid[1]'";
            $rst = Yii::$app->db->createCommand($q)->queryAll(false);
            foreach ($rst as $rs)
			{
                $data[$rs[0]] = $rs[1];
            }
        }
	  
	  return $data;
	}
	
	public function getSpecs()
	{
	  $data = [];
	 
	    $data[''] = 'Select';
		$data['BONELESS'] = 'BONELESS';
		$data['BONE IN'] = 'BONE IN';
	  
	  return $data;
	}


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cicode' => 'Cutting Preparation Document No',
            'supid' => 'Supplier',
            'oprodid' => 'PRODUCT',
            'quantity' => 'Quantity',
            'weight' => 'WEIGHT',
            'spec' => 'SPEC',
            'cdate' => 'Cdate',
            'ctime' => 'Ctime',
            'printed' => 'Printed',
            'pby' => 'Pby',
            'pdate' => 'Pdate',
            'ptime' => 'Ptime',
            'nweight' => 'Nweight',
            'uby' => 'Uby',
            'udate' => 'Udate',
        ];
    }
}
