<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "employees".
 *
 * @property int $empid
 * @property string $empcode
 * @property string $fname
 * @property string $mname
 * @property string $sname
 * @property string $gender
 * @property string $email
 * @property int $titleid
 * @property string $telno
 * @property string $pic
 * @property int $zid
 * @property string $uname
 * @property string $pwd
 * @property string $atoken
 * @property string $status
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 * @property int $reportsto
 * @property string $llogindate
 */
class Employees extends \yii\db\ActiveRecord
{
	public $role;
    public $fdate;
	public $tdate;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'employees';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fname', 'sname', 'empcode', 'gender', 'email',  'zid', 'telno', 'uname', 'pwd', 'role', 'fdate', 'tdate','titleid', 'reportsto'], 'required'],
			[['email', 'empcode', 'uname'], 'unique'],
            [['email'], 'email'],
			[['fname', 'sname', 'empcode', 'gender', 'email',  'zid', 'telno', 'uname', 'pwd', 'role', 'fdate', 'tdate','titleid', 'reportsto'], 'required', 'on' => 'create'],
			['fdate', 'compare', 'compareAttribute' => 'tdate', 'operator' => '<=', 'enableClientValidation' => false],
			['fdate', 'check'],
			['tdate', 'chck'],
			[['fname', 'sname', 'empcode', 'gender', 'email',  'zid', 'telno', 'uname', 'pwd', 'role', 'fdate', 'tdate', 'pwd','titleid', 'reportsto'], 'safe'],
        ];
    }
	
	public function scenarios()
    {
		$scenarios = parent::scenarios();
        $scenarios['update'] = ['fname', 'mname', 'sname', 'empcode', 'gender', 'email',  'zid', 'telno','titleid', 'reportsto'];//Scenario Values Only Accepted
        return $scenarios;
    }
	
	public function Emp($empid)
	{
		$q = "SELECT CONCAT(fname,' ',IFNULL(mname,''),' ',sname) FROM employees WHERE empid = '$empid' ";
		$emp = Yii::$app->db->createCommand($q)->queryScalar();
		return $emp;
	}
	
	public function check($attribute,$params)
	  {
		$query="SELECT CURDATE() ";
		$validator =Yii::$app->db->createCommand($query)->queryScalar();
		$dt = explode('-',$validator);
		$validator = $dt[2].'/'.$dt[1].'/'.$dt[0];
		
		if($this->fdate < $validator)
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
            'empid' => 'Empid',
            'empcode' => 'Employee Code',
            'fname' => 'First Name',
            'mname' => 'Middle Name',
            'sname' => 'Last Name',
            'gender' => 'Gender',
            'email' => 'Email',
            'titleid' => 'Title',
            'telno' => 'Phone Number',
            'pic' => 'Picture',
            'zid' => 'Zone',
            'uname' => 'Username',
            'pwd' => 'Password',
            'atoken' => 'Atoken',
            'status' => 'Status',
            'cby' => 'Cby',
            'cdate' => 'Cdate',
			'fdate' => 'From',
            'tdate' => 'To',
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
	
	public function theZone($zid)
	{
		$query = "SELECT name FROM zones WHERE zid = '$zid' ";
		$zone = Yii::$app->db->createCommand($query)->queryScalar();
		return $zone;
	}
	
	public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->cby = Yii::$app->user->id;
				$this->cdate = new Expression('NOW()');
				$this->status = 'A';
				$this->pwd = Yii::$app->getSecurity()->generatePasswordHash($this->pwd);
            }
            return true;
        }
        return false;
    }
}
