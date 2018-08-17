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
class Manifest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
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
            [['blno','bltype'], 'required'],
           // [['cbm'], 'number','min'=>0.1],
			//[['cno'],'chkCno'],
			//[['gid'],'chkCBM'],
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
	
    public function attributeLabels()
    {
        return [
            'blno' => 'BL #',
            'bltype' => 'BL Type',
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
