<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jobdocuments".
 *
 * @property int $id
 * @property string $jid
 * @property int $docid
 * @property string $docattach
 * @property string $hascharges
 * @property string $charges
 * @property string $inusd
 * @property string $erate
 * @property string $hasvat
 * @property string $vatamt
 * @property string $vatinusd
 * @property string $paidin
 * @property string $paidby
 * @property string $invoicenum
 * @property string $paid
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Jobdocuments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jobdocuments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['jid', 'docid', 'hascharges', 'paidby'], 'required'],
            ['charges', 'number','min'=>0],
			['hascharges', 'chkValue'],
			['hascharges', 'chkValue2'],
			['hascharges', 'chkValue3'],
			['docattach', 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, pdf','maxSize'=>4194308],
			[['charges','paidin','hasvat'],'safe'],
        ];
    }
	
	public function chkValue($attribute,$params)
	{
		if($this->hascharges =='Y')
		{
			if(empty($this->charges))
			{
				$this->addError('charges','Paid Amount is Required');
			}
		}
	}
	
	public function chkValue2($attribute,$params)
	{
		if($this->hascharges =='Y')
		{
			if(empty($this->paidin))
			{
				$this->addError('paidin','Paid Currency is Required');
			}
		}
	}
	
	public function chkValue3($attribute,$params)
	{
		if($this->hascharges =='Y')
		{
			if(empty($this->hasvat))
			{
				$this->addError('hasvat','Please specify if this charges has VAT');
			}
		}
	}
	
	 public function getCert($jid)
	 {
	  $data = [];
	  $q ="SELECT docid,name FROM documents WHERE NOT docid IN(SELECT docid FROM jobdocuments WHERE jid='$jid') AND docid > 11";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(false);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	 }
	 
	 public function getDoc($jid)
	 {
	  $data = [];
	  $q ="SELECT docid,name FROM documents WHERE NOT docid IN(SELECT docid FROM jobdocuments WHERE jid='$jid') AND docid IN(6,7,8,9,10,11)";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(false);
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	 }
	 
	  public function getPBy($jid)
	 {
	  $data = array();
	  $client = Yii::$app->db->createCommand("SELECT client FROM jobs WHERE jid = '$jid'")->queryScalar();
	  $q ="SELECT cf.cname FROM companyinfo cf INNER JOIN jobs j ON cf.cid = j.cfid WHERE j.jid ='$jid'";
	  $rslt = Yii::$app->db->createCommand($q)->queryAll(false);
	  
	  foreach($rslt as $rs)
	  {
	    $data[$rs[0]] = $rs[0];
	  }
	  $data[$client] = $client;
	  return $data;
	 }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
			'jid' => 'Jid',
			'docid' => 'Document type',
			'docattach' => 'Supporting Document',
			'hascharges' => 'Has Charges',
			'charges' => 'Amount Paid',
			'paidby' => 'Paid/Processed By',
			'paidin' => 'Paid In',
			'cby' => 'Cby',
			'cdate' => 'Cdate',
			'eby' => 'Eby',
			'edate' => 'Edate',
        ];
    }
	
	 public function beforeSave($insert)
	 {
	   if(parent::beforeSave($insert))  //call parent method so that the events are fired appropriately
	   {
		    $this->erate = Yii::$app->user->identity->rate;
			 if($this->hascharges =='Y')
			 {
				 if($this->paidin == 'USD')
				 {
					 $this->inusd = $this->charges;
					 $this->charges = $this->inusd * $this->erate;
					  if($this->hasvat == 'Y')
				      {
					   $this->vatinusd =  $this->inusd * 0.18;
					   $this->vatamt =  $this->charges * 0.18;
				      }
				 }
				 else
				 {
					 $this->inusd = $this->charges / $this->erate;
					 if($this->hasvat == 'Y')
				      {
					   $this->vatinusd =  $this->inusd * 0.18;
					   $this->vatamt =  $this->charges * 0.18;
				      }
				 }
			 }
			
		 if($this->isNewRecord)
		 {
			$this->cby = Yii::$app->user->id;
			$this->cdate = new \yii\db\Expression('NOW()');
		 }
		 else
		 {
			$this->eby = Yii::$app->user->id;
			$this->edate = new \yii\db\Expression('NOW()');
		 }
		 return true;
	   }
	   return false;
	 }
}
