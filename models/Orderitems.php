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
class Orderitems extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	 public $pcalc;
	 public $nopieces;
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
            [['gid','iid','nop','nopieces'], 'required'],
			 [['nop','nopieces'], 'integer','min'=>1],
			[['descr'],'safe'],
        ];
    }
	
	public function chkCBM($attribute,$params)
	 {
		   $gid = $this->gid;
		   if(!empty($gid))
		   {
		     $exp = explode(":",$gid);
			 
	        if(($exp[1] == 'CBM') && ($this->pcalc == 'NOW'))
		    {
		      if(empty($this->cbm))
			  {
			  $this->addError('cbm',"Total CBM is Required");
			  }
		    }
			
		  }
	 }
	
	
	public function chkCno($attribute,$params)
	 {
		   $cno = trim($this->cno);
		   if(!empty($cno))
		   {
		   $conn = Yii::$app->db;
		   $rs = $conn->createCommand("SELECT COUNT(*) FROM orderitems WHERE cno ='$cno'")->queryScalar(); 
	        if($rs > 0)
		    {
		     $this->addError('cno',"This Control Number Has already been used");
		    }
			else
			{
				$rs = $conn->createCommand("SELECT COUNT(*) FROM osqueezed WHERE cno ='$cno'")->queryScalar(); 
	            if($rs > 0)
		        {
		          $this->addError('cno',"This Control Number Has already been used");
		        }
				else
				{
					$rs = $conn->createCommand("SELECT COUNT(*) FROM osqueezed WHERE ncno ='$cno'")->queryScalar(); 
	                 if($rs > 0)
		             {
		              $this->addError('cno',"This Control Number Has already been used");
		             }
				}
			}
		   }
	 }
	 
	 public function getGitems($gid)
	{
	  $data = [];
	  
			if(!empty($gid))
			{
				$exp = explode(":",$gid);
				$q = "SELECT CONCAT(gid,':',cper),CONCAT(name,' - ',cper) FROM itemgroup WHERE gid = '$gid' ORDER BY name";	
			}
			else
			{
			 $q = "SELECT CONCAT(gid,':',cper),CONCAT(name,' - ',cper) FROM itemgroup WHERE cper = 'CBM' ORDER BY name";	
			}
	  
	  $rst = Yii::$app->db->createCommand($q)->queryAll(false);
	  foreach($rst as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	 
	
	public function getItems()
	{
	  $data = [];
	   if (Yii::$app->request->post('Orderitems')['gid']) 
		{
			$pid = Yii::$app->request->post('Orderitems')['gid'];
			$exp = explode(":",$pid);
	        $rst = Yii::$app->db->createCommand("SELECT iid,name FROM items WHERE gid ='$exp[0]' ORDER BY name")->queryAll(false);
			  foreach($rst as $rs)
			  {
				$data[$rs[0]] = $rs[1];
			  }
		}
	 return $data;
	}
	
	public function getItemsP()
	{
	  $data = [];
	  $rslt = Yii::$app->db->createCommand("SELECT CONCAT(itemid,':',prodid),name FROM products ORDER BY name")->queryAll(0);
	  foreach($rslt as $rs)
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
            'iid' => 'Item Name',
            'orderno' => 'Orderno',
            'cbm' => '# Of CBM/Bundle',
            'weight' => 'Weight(KG)',
            'dweight' => 'Dweight',
            'price' => 'Item Price',
            'eddate' => 'Estmated Delivery Date',
            'quantity' => 'Quantity',
            'locid' => 'Locid',
            'cno' => 'Control Number',
            'pcalc' => 'Price Calculation',
            'dby' => 'Dby',
            'creason' => 'Creason',
            'canby' => 'Canby',
            'candate' => 'Candate',
            'status' => 'Status',
            'chopped' => 'Chopped',
            'nop' => 'No Of Items',
	    'descr' => 'Package Description',
            'nopieces' => 'No Of Pieces',
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
