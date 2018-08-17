<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jobs".
 *
 * @property string $jid
 * @property int $cfid
 * @property int $cid
 * @property string $blno
 * @property int $itype
 * @property int $quantity
 * @property string $bltype
 * @property string $country
 * @property int $sid
 * @property int $icd
 * @property int $stid
 * @property string $tansno
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Jobsrch extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jobs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['jid'], 'required'],
            [['tansno'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'jid' => 'Search Item',
            'cfid' => 'Job Handled By',
            'cid' => 'Customer Name',
            'blno' => 'Blno',
            'itype' => 'Itype',
            'quantity' => 'Quantity',
            'bltype' => 'Bltype',
            'country' => 'Country',
            'sid' => 'Sid',
            'icd' => 'ICD',
            'stid' => 'Stid',
            'tansno' => 'Tansard #',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
            'eby' => 'Eby',
            'edate' => 'Edate',
        ];
    }
	
	public function getClients()
	{
	  $data = [];
	  $rst = Yii::$app->db->createCommand("SELECT cid,cname FROM companyinfo WHERE cid IN(1)")->queryAll(false);
	  foreach($rst as $rs)
	  {
	    $data[$rs[1]] = $rs[1];
	  }
	  return $data;
	}
	
	 public function getCF()
	{
	  $data = [];
	  $rst = Yii::$app->db->createCommand("SELECT cid,cname FROM companyinfo WHERE cid IN(4)")->queryAll(false);
	  foreach($rst as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
	
	 public function getICD()
	{
	  $data = [];
	  $rst = Yii::$app->db->createCommand("SELECT icd,name FROM icds ORDER BY name")->queryAll(false);
	  foreach($rst as $rs)
	  {
	    $data[$rs[0]] = $rs[1];
	  }
	  return $data;
	}
}
