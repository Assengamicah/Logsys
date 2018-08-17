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
class Chops4 extends \yii\db\ActiveRecord
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
            [['nweight','prodid','barcode'], 'required'],
			['weight','number','min'=>0.1],
			[['fq','carcass'],'safe'],
			['barcode','chkValidity'],
        ];
    }
	
	public function chkValidity($attribute,$params)
	 {
	    
		  $conn = Yii::$app->db;
	      $bc = trim($this->barcode);
		  $id = $this->prodid; 
		  if($this->isNewRecord) 
		  {
	        if(!empty($id))
		    {
			  $pid = $conn->createCommand("SELECT prodid FROM chops WHERE id ='$id'")->queryScalar(); 
			  $ipo = $conn->createCommand("SELECT COUNT(barcode) FROM barcodes WHERE barcode ='$bc'")->queryScalar(); 
               if($ipo == 1)
			   {
				   //Has it been used ?
			 $used = $conn->createCommand("SELECT COUNT(barcode) FROM barcodes WHERE barcode ='$bc' AND used = 'Y'")->queryScalar(); 
			        if($used > 0)
					{
						$this->addError('barcode',"This Barcode Has Already Been Used.Please Apply New Barcode to Proceed");
					}
					else
					{
					//Is it from the same product Family ?
					$qp = "SELECT COUNT(prodid) FROM barcodes WHERE barcode = '$bc' AND prodid = '$pid'";
					
					$sm = $conn->createCommand($qp)->queryScalar();
					if($sm == 0)
					{
						$this->addError('barcode',"This Barcode Is of the Different Product. Please Apply Barcode for Product of this Goods");
					}
					}
			   }
               else
			   {
				   $this->addError('barcode',"Provided Barcode For This Product Is not Correct");
			   }				   
		     
		    }
		  }
		  else
		  {
			$obarcode = Yii::$app->db->createCommand("SELECT barcode FROM chops WHERE id ='$id'")->queryScalar(); 
            if($this->barcode != $obarcode)
			{
				$pid = $conn->createCommand("SELECT prodid FROM chops WHERE id ='$id'")->queryScalar(); 
			    $ipo = $conn->createCommand("SELECT COUNT(barcode) FROM barcodes WHERE barcode ='$bc'")->queryScalar(); 
               if($ipo == 1)
			   {
				   //Has it been used?
			      $used = $conn->createCommand("SELECT COUNT(barcode) FROM barcodes WHERE barcode ='$bc' AND used = 'Y'")->queryScalar(); 
			        if($used > 0)
					{
						$this->addError('barcode',"This Barcode Has Already Been Used.Please Apply New Barcode to Proceed");
					}
					else
					{
					//Is it from the same product Family ?
					$qp = "SELECT COUNT(prodid) FROM barcodes WHERE barcode = '$bc' AND prodid = '$pid'";
					
					$sm = $conn->createCommand($qp)->queryScalar();
					if($sm == 0)
					{
						$this->addError('barcode',"This Barcode Is of the Different Product. Please Apply Barcode for Product of this Goods");
					}
					}
			   }
               else
			   {
				   $this->addError('barcode',"Provided Barcode For This Product Is not Correct");
			   }				  
			}				
		  }
	 }
	 
	public function getPUpdate()
	{
	  $data = [];
	  $nat = $_SESSION['instrcode'];
	   if($this->isNewRecord) 
		{
			$q = "SELECT c.id,CONCAT(p.name,' ',spec) FROM chops c INNER JOIN products p ON p.prodid = c.prodid  WHERE ";
			$q .="c.instrcode = '$nat' AND c.status = 'A' AND c.nweight IS NULL ";
		}
		else
		{
			$q = "SELECT c.id,CONCAT(p.name,' ',spec) FROM chops c INNER JOIN products p ON p.prodid = c.prodid  WHERE ";
			$q .="c.id = '$this->id' AND c.status = 'A'";
		}
		
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(0);
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
            'prodid' => 'Cut Specification',
            'nweight' => 'After Cut New Weight(KG)',
            'fq' => 'FQ %',
            'carcass' => 'CARCASS %',
            'weight' => 'WEIGHT',
            'spec' => 'SPEC',
            'cdate' => 'Cdate',
            'ctime' => 'Ctime',
            'printed' => 'Printed',
            'barcode' => 'Barcode',
            'pdate' => 'Pdate',
            'ptime' => 'Ptime',
            'uby' => 'Uby',
            'udate' => 'Udate',
        ];
    }
	
	 public function doupdate()
	 {
	   $id = Yii::$app->user->id;
	   $q = "UPDATE chops SET nweight ='$this->nweight',fq ='$this->fq',carcass ='$this->carcass',barcode ='$this->barcode',";
	   $q .="uby = '$id',udate = NOW() WHERE id = '$this->prodid'";
	   
	   $q2 = "UPDATE barcodes SET used = 'Y' WHERE barcode = '$this->barcode'";
	   
	   Yii::$app->db->createCommand($q)->execute();
	   Yii::$app->db->createCommand($q2)->execute();
	   
			
	 }
}
