<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;
use yii\db\ActiveRecord;

class User extends ActiveRecord implements IdentityInterface
{
   
    
    public static function tableName()
	{
		return 'employees';
	}
	

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

 
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['atoken' => $token]);
  
    }

   
    public static function findByUname($uname)
    {
        return static::findOne(['uname' => $uname]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->empid;
    }
	
	public function getcurrYear()
    {
        //return \Yii::$app->user->identity->fname;
		return Yii::$app->db->createCommand("SELECT foryear FROM fyear WHERE isactive ='Y'")->queryScalar();
    }
	
	public function getRate()
    {
        //return \Yii::$app->user->identity->fname;
		return Yii::$app->db->createCommand("SELECT erate FROM exchangerate WHERE status ='C'")->queryScalar();
    }
	
	public function getFullname()
    {
        //return \Yii::$app->user->identity->fname;
		//return $this->fname.' '.$this->mname.' '.$this->sname;
		return $this->uname;
    }
	
	public function getCid()
    {
        //return \Yii::$app->user->identity->fname;
		//return $this->fname.' '.$this->mname.' '.$this->sname;
		return $this->cid;
    }
	
	public function getFn()
    {
        //return \Yii::$app->user->identity->fname;
		return $this->fname.' '.$this->mname.' '.$this->sname;
		
    }
	
	public function getEmail()
    {
        //return \Yii::$app->user->identity->fname;
		return $this->email;
    }
	
	public function getPic()
    {
        return $this->pic;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password,$this->pwd);
    }
	
	public function isActive()
    {
        if($this->status == 'A')
		{
			return true;
		}
		return false;
    }
}
