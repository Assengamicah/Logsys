<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "orderitems".
 *
 * @property int $id
 * @property string $itemid
 * @property string $orderno
 * @property int $prodid
 * @property string $weight
 * @property string $dweight
 * @property string $price
 * @property string $ddate
 * @property string $dtime
 * @property int $locid
 * @property int $cby
 * @property string $cdate
 * @property int $dby
 * @property string $creason
 * @property int $canby
 * @property string $candate
 * @property string $status
 * @property string $chopped
 * @property string $rweight
 */
class Release extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $name;
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
            [['rlno'], 'required'],
			[['rlno'], 'chkRl'],
        ];
    }
	
	
	 public function chkRl($attribute,$params)
	 {
	    $rl = trim($this->rlno);
		if(!empty($rl))
		  {
		   $ipo = Yii::$app->db->createCommand("SELECT COUNT(*) FROM orderitems WHERE rlno='$rl'")->queryScalar();
			if($ipo > 0)
			 {
			   //haijatoka
			   $ip = Yii::$app->db->createCommand("SELECT COUNT(*) FROM orderitems WHERE rlno='$rl' AND cleared ='Y'")->queryScalar();
			    if($ip > 0)
				{
				  $gh = "SELECT CONCAT(e.fname,' ',e.mname,' ',e.sname),DATE_FORMAT(oi.cldate,'%d/%m/%Y'),";
		          $gh .="DATE_FORMAT(oi.cldate,'%H:%i:%s') FROM orderitems oi INNER JOIN employees e ON e.empid = oi.clby ";
                  $gh .="WHERE oi.rlno ='$rl' LIMIT 1";
                  $rs = Yii::$app->db->createCommand($gh)->queryOne(false);				  
			      $this->addError('rlno',"Items In The Provided Release Number Have Already Been Cleared.Cleared By $rs[0], Cleared Date $rs[1] and Cleared Time $rs[2]");
				}
			 }
			else
			{
				 $this->addError('rlno','Release Number Does Not Exist.Please Try Again By Type It Correclty');
			}
		  }
		  
	 } 
	
	public function getItems()
	{
	  $data = [];
	  $rst = Yii::$app->db->createCommand("SELECT CONCAT(iid,':',cper),CONCAT(name,' - ',cper) FROM items WHERE inext = 'E' ORDER BY name")->queryAll(false);
	  foreach($rst as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	
	
	

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rlno' => 'Release Number',
            'orderno' => 'Orderno',
            'cbm' => 'CBM',
            'weight' => 'Weight(KG)',
            'dweight' => 'Dweight',
            'price' => 'Item Price',
            'eddate' => 'Estmated Delivery Date',
            'quantity' => 'Quantity',
            'locid' => 'Locid',
            'cno' => 'Control Number',
            'cdate' => 'Cdate',
            'dby' => 'Dby',
            'creason' => 'Creason',
            'canby' => 'Canby',
            'candate' => 'Candate',
            'status' => 'Status',
            'chopped' => 'Chopped',
            'munit' => 'Unit Measure',
        ];
    }
	
	public function getUnit()
	{
		$data = [];
		$data['g'] = 'Gram';
		$data['kg'] = 'Kilogram';
		return $data;
	}
}
