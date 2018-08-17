<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "userrole".
 *
 * @property int $id
 * @property int $rid
 * @property int $userid
 * @property string $fdate
 * @property string $tdate
 * @property int $cby
 * @property string $cdate
 */
class Userrole extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'userrole';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rid', 'userid', 'fdate', 'tdate'], 'required'],
			//['fdate', 'compare', 'compareAttribute' => 'tdate', 'operator' => '<=', 'enableClientValidation' => false],
			['fdate', 'check'],
			['fdate', 'cmp'],
			//['tdate', 'chck'],
			[['fdate', 'tdate', 'cdate'], 'safe'],
        ];
    }
	
	public function check($attribute,$params)
	  {
		$query="SELECT CURDATE() ";
		$validator =Yii::$app->db->createCommand($query)->queryScalar();
		$dt = explode('-',$validator);
		$validator = $dt[2].'/'.$dt[1].'/'.$dt[0];
		
		if($this->fdate < $validator OR $this->tdate < $validator)
		{
			$this->addError('fdate',Yii::t('app','From date must be greater or equal to todays date '));
		}
		 
	  }
	  
	  public function cmp($attribute,$params)
	  {  
		if($this->fdate > $this->tdate)
		{
			$this->addError('fdate',Yii::t('app','From date must be greater or equal to todays date '));
		}
		 
	  }
	  
	  public function chck($attribute,$params)
	  {
		$query="SELECT CURDATE() ";
		$validator =Yii::$app->db->createCommand($query)->queryScalar();
		$dt = explode('-',$validator);
		$validator = $dt[2].'/'.$dt[1].'/'.$dt[0]; 
		
		if($this->tdate < $validator)
		{
			$this->addError('tdate',Yii::t('app','To date must be greater or equal to todays date '));
		}
		 
	  }
	  

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rid' => 'Role',
            'userid' => 'Staff',
            'fdate' => 'From',
            'tdate' => 'To',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
        ];
    }
	
	public function Users()
	{
		$users = array();
		$query = "SELECT empid,CONCAT(fname,' ',IFNULL(mname,''),' ',sname) FROM employees WHERE status = 'A'";
		$rst = Yii::$app->db->createCommand($query)->queryAll(false);
		foreach($rst as $rs)
		{
			$users[$rs[0]] = $rs[1];
		}
		return $users;
	}
	
	public function getRole()
	{
		$tittles = array();
		$rst = Yii::$app->db->createCommand('SELECT rid,name FROM roles')->queryAll(false);
		foreach($rst as $rs)
		{
			$tittles[$rs[0]] = $rs[1];
		}
		return $tittles;
	}
	
	public function Emp($empid)
	{
		$q = "SELECT CONCAT(fname,' ',IFNULL(mname,''),' ',sname) FROM employees WHERE empid = '$empid' ";
		$emp = Yii::$app->db->createCommand($q)->queryScalar();
		return $emp;
	}
	
	public function Role($rid)
	{
		$q = "SELECT name FROM roles WHERE rid = '$rid' ";
		$role = Yii::$app->db->createCommand($q)->queryScalar();
		return $role;
	}
	
	public function format($date)
	{
		$dt = explode('-',$date);
		$date = $dt[2].'/'.$dt[1].'/'.$dt[0];
		return $date;
	}
	
	public function format1($datetime)
	{
		$date = explode(' ',$datetime);
		$dt = explode('-',$date[0]);
		$datetime = $dt[2].'/'.$dt[1].'/'.$dt[0];
		return $datetime;
	}
}
