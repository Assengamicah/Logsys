<?php

namespace app\models;

use Yii;
use yii\db\Expression;

class Userp extends \yii\db\ActiveRecord
{
	public $passwd;
	public $cpwd;
	
	
    public static function tableName()
    {
        return 'employees';
    }

   
    public function rules()
    {
        return [
            [['fname', 'sname', 'gender', 'email','telno','passwd','cpwd'], 'required'],
			[['email'], 'unique'],
            [['email'], 'email'],
			['cpwd', 'compare','compareAttribute'=>'passwd','message'=>'Password Did not Match'],
	
			[['mname'], 'safe'],
        ];
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
            'cby' => 'Cby',
            'cdate' => 'Cdate',
			'fdate' => 'Role Start Date',
            'tdate' => 'Role End Date',
            'eby' => 'Eby',
            'edate' => 'Edate',
            'reportsto' => 'Reports To',
            'llogindate' => 'Llogindate',
			'passwd' => 'Password',
			'cpwd' => 'Confirm Password',
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


	
	public function beforeSave($insert)
	 {
	   if(parent::beforeSave($insert))  //call parent method so that the events are fired appropriately
	   {
		    
		if($this->isNewRecord)
		 {
			$this->pwd = Yii::$app->security->generatePasswordHash($this->pwd);
		 }
		 else
		 {
			if($this->passwd != $this->pwd)
			{
			 $this->pwd = Yii::$app->security->generatePasswordHash($this->passwd);
			}
			$this->edate = new \yii\db\Expression('NOW()');
		 }
		 return true;
	   }
	   return false;
	 }
}
