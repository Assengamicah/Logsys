<?php

namespace app\models;

use Yii;
use yii\db\Expression;

class Userrole extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'userrole';
    }


    public function rules()
    {
        return [
            [['rid', 'userid', 'fdate', 'tdate'], 'required'],
			['fdate', 'checkDate'],
			[['fdate', 'tdate', 'cdate'], 'safe'],
        ];
    }
	
	public function checkDate($attribute,$params)
	{
		$dt = explode('/',$this->fdate);
		$fdate = $dt[2].'-'.$dt[1].'-'.$dt[0];
		
		$dt1 = explode('/',$this->tdate);
		$tdate = $dt1[2].'-'.$dt1[1].'-'.$dt1[0];
		
		$validator =Yii::$app->db->createCommand("SELECT DATEDIFF('$tdate',CURDATE()) ")->queryScalar();
		$validator2 =Yii::$app->db->createCommand("SELECT DATEDIFF('$fdate',CURDATE()) ")->queryScalar();
		
		if($validator >= 0 AND $validator2 >= 0)
		{
			$validator1 =Yii::$app->db->createCommand("SELECT DATEDIFF('$tdate','$fdate')")->queryScalar();
			 if($validator1 < 0)
			 {
				$this->addError('fdate',Yii::t('app','Role Start date can not be greater than Role End Date '));
			 }
		}
		else
		{
			$this->addError('fdate',Yii::t('app','Role Start date and Role End date must be greater or equal to todays date '));
		} 
	}
	  

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rid' => 'Role',
            'userid' => 'Staff',
            'fdate' => 'Role From Date',
            'tdate' => 'Role To Date',
            'cby' => 'Created by',
            'cdate' => 'Created date',
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
