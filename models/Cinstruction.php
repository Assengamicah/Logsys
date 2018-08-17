<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cinstruction".
 *
 * @property int $id
 * @property string $cicode
 * @property int $oprodid
 * @property int $prodid
 * @property int $quantity
 * @property string $spec
 * @property string $tweight
 * @property string $twunit
 * @property string $cweight
 * @property string $cwunit
 * @property string $carcass
 * @property string $fq
 * @property int $cby
 * @property string $cdate
 * @property string $ctime
 * @property int $uby
 * @property string $udate
 * @property string $utime
 * @property string $printed
 * @property int $pby
 * @property string $pdate
 * @property string $ptime
 * @property string $isdone
 */
class Cinstruction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'cinstruction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prodid', 'cweight', 'cwunit', 'pweight', 'pwunit'], 'required'],
            [['cweight', 'pweight'], 'number','min'=>0.01],
            ['cweight', 'chkWeight'],
			['pweight', 'chkPWeight'],
        ];
    }
	
	public function chkWeight($attribute,$params)
	 {
		  $conn = Yii::$app->db;
		  $cicode = $_SESSION['cicode'];
		  $cid = explode(":",$this->prodid);
		  $q = "SELECT tweight,twunit FROM cinstruction WHERE cicode ='$cicode' AND oprodid ='$cid[1]' LIMIT 1";
		  $dt = Yii::$app->db->createCommand($q)->queryOne(0);
		  
		  $qd = "SELECT tmpweight,tmpwunit FROM cinstruction WHERE cicode ='$cicode' AND oprodid ='$cid[1]' AND tmpweight > 0";
		  $rst = Yii::$app->db->createCommand($qd)->queryAll(false);
		  $tw = 0;
	        if(!empty($rst))
		    {
				
				foreach($rst as $rs)
				{
					if($rs[1] == $dt[1])
					{
						$tw = $tw + $rs[0];
					}
					elseif(($dt[1] == 'g') && ($rs[1] == 'kg'))
					{
						$tw = $tw + ($rs[1] * 1000);
					}
					else
					{
						$tw = $tw + ($rs[1] / 1000);
					}
				}
				
					
			}
			
			$cunit = $this->cwunit;
				if($dt[1] == $cunit)
					{
						$tw = $tw + $this->cweight;
					}
					elseif(($dt[1] == 'g') && ($cunit == 'kg'))
					{
						$tw = $tw + ($this->cweight * 1000);
					}
					else
					{
						$tw = $tw + ($this->cweight / 1000);
					}
				
				if($tw > $dt[0])
				{
			     $this->addError('cweight',"Total After Cut Weight Can Not Exceed Product Cut Weight");
				}
               
	 }
	 
	 public function chkPWeight($attribute,$params)
	 {
		  
	        $punit = $this->pwunit;
			$cunit = $this->cwunit;
				if($punit == $cunit)
					{
						$tw = $this->pweight;
					}
					elseif(($cunit == 'g') && ($punit == 'kg'))
					{
						$tw = $this->pweight * 1000;
					}
					else
					{
						$tw = $this->pweight / 1000;
					}
				
				if($tw > $this->cweight)
				{
			     $this->addError('pweight',"Packed Weight Can Not Exceed Cut Weight");
				}
               
	 }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cicode' => 'Cicode',
            'oprodid' => 'Oprodid',
            'prodid' => 'Cut',
            'quantity' => 'Quantity',
            'spec' => 'Spec',
            'tweight' => 'Tweight',
            'twunit' => 'Twunit',
            'cweight' => 'Cut Weight',
            'cwunit' => 'Cwunit',
            'carcass' => 'Carcass',
            'fq' => 'Fq',
            'pweight' => 'Packed Weight',
            'cdate' => 'Cdate',
            'ctime' => 'Ctime',
            'packed' => 'Packed Into',
            'udate' => 'Udate',
            'utime' => 'Utime',
            'printed' => 'Printed',
            'pby' => 'Pby',
            'pdate' => 'Pdate',
            'ptime' => 'Ptime',
            'isdone' => 'Isdone',
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
