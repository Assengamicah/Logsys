<?php

namespace app\models;

use Yii;
use yii\db\Expression;

class Employees extends \yii\db\ActiveRecord
{
	public $role;
    public $fdate;
	public $tdate;
	
	
    public static function tableName()
    {
        return 'employees';
    }

   
    public function rules()
    {
        return [
            [['fname', 'sname', 'cid', 'gender', 'email', 'telno', 'uname', 'role', 'fdate', 'tdate'], 'required'],
			[['email', 'uname'], 'unique'],
            [['email'], 'email'],
			[['tdate'], 'checkDate'],
			[['fname', 'sname', 'cid', 'gender', 'email', 'telno', 'uname', 'role', 'fdate', 'tdate'], 'required', 'on' => 'create'],
	        [['telno'],'match','pattern'=>'/^[0-9]+$/'],
			[['fname', 'mname', 'sname', 'empcode', 'gender', 'email', 'telno', 'uname', 'role', 'pwd'], 'safe'],
        ];
    }
	
	public function scenarios()
    {
		$scenarios = parent::scenarios();
        $scenarios['update'] = ['fname', 'cid', 'sname', 'gender', 'email', 'telno'];//Scenario Values Only Accepted
        return $scenarios;
    }
	
	
	public function Emp($empid)
	{
		$q = "SELECT CONCAT(fname,' ',IFNULL(mname,''),' ',sname) FROM employees WHERE empid = '$empid' ";
		$emp = Yii::$app->db->createCommand($q)->queryScalar();
		return $emp;
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
            'empid' => 'Empid',
            'empcode' => 'Employee Code',
            'fname' => 'First Name',
            'mname' => 'Middle Name',
            'sname' => 'Last Name',
            'gender' => 'Gender',
            'email' => 'Email',
            'titleid' => 'Jobtitle',
            'telno' => 'Phone Number',
            'pic' => 'Picture',
            'zid' => 'Office',
            'uname' => 'Username',
            'pwd' => 'Password',
            'atoken' => 'Atoken',
            'status' => 'Status',
            'cid' => 'Employee Working Country',
            'cdate' => 'Cdate',
			'fdate' => 'Role Start Date',
            'tdate' => 'Role End Date',
            'eby' => 'Eby',
            'edate' => 'Edate',
            'reportsto' => 'Reports To',
            'llogindate' => 'Llogindate',
        ];
    }
	
	public function getTittle()
	{
		$tittles = array();
		$rst = Yii::$app->db->createCommand('SELECT titleid,name FROM jobtitles')->queryAll(false);
		foreach($rst as $rs)
		{
			$tittles[$rs[0]] = $rs[1];
		}
		return $tittles;
	}
	
	public function getZone()
	{
		$tittles = array();
		$rst = Yii::$app->db->createCommand('SELECT zid,name FROM zones')->queryAll(false);
		foreach($rst as $rs)
		{
			$tittles[$rs[0]] = $rs[1];
		}
		return $tittles;
	}
	
	public function getCountries()
	{
		$tittles = array();
		$rst = Yii::$app->db->createCommand("SELECT cid,cname FROM countries WHERE cid IN(1,44,224)")->queryAll(false);
		foreach($rst as $rs)
		{
			$tittles[$rs[0]] = $rs[1];
		}
		return $tittles;
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
	
	public function theTitle($titleid)
	{
		$query = "SELECT name FROM jobtitles WHERE titleid = '$titleid' ";
		$title = Yii::$app->db->createCommand($query)->queryScalar();
		return $title;
	}
	
	public function country($cid)
	{
		$query = "SELECT cname FROM countries WHERE cid = '$cid' ";
		$country = Yii::$app->db->createCommand($query)->queryScalar();
		return $country;
	}
	
	public function theZone($zid)
	{
		$query = "SELECT name FROM zones WHERE zid = '$zid' ";
		$zone = Yii::$app->db->createCommand($query)->queryScalar();
		return $zone;
	}
	
	public function format1($datetime)
	{
		$date = explode(' ',$datetime);
		$dt = explode('-',$date[0]);
		$datetime = $dt[2].'/'.$dt[1].'/'.$dt[0];
		return $datetime;
	}
	
	public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) 
		{
            if ($this->isNewRecord) 
			{
                $empcode = Yii::$app->db->createCommand("SELECT MAX(empid) + 1 FROM employees")->queryScalar();
				$this->empcode = $empcode;
				$this->cby = Yii::$app->user->id;
				$this->cdate = new Expression('NOW()');
				$this->status = 'A';
				$pwd = 'password';
				$this->pwd = Yii::$app->getSecurity()->generatePasswordHash($pwd);
            }
            return true;
        }
        return false;
    }
}
