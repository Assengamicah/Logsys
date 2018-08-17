<?php

namespace app\models;

use Yii;
use yii\base\Model;

class FrostanRoles extends Model
{
	public static function isAdmin()
	{
	  $id = Yii::$app->user->id;
	  $conn = Yii::$app->db;
	  $q = "SELECT COUNT(id) FROM userrole WHERE userid='$id' AND rid IN(0,7)";
	  $role = $conn->createCommand($q)->queryScalar();
	 if($role > 0)
	  {
	    return true;
	  }
	  return false;
	}
	
	public static function isManager()
	{
	  $id = Yii::$app->user->id;
	  $conn = Yii::$app->db;
	  $q = "SELECT COUNT(id) FROM userrole WHERE userid='$id' AND rid IN(0,1)";
	  $role = $conn->createCommand($q)->queryScalar();
	 if($role > 0)
	  {
	    return true;
	  }
	  return false;
	}
	
	public static function isQA()
	{
	  $id = Yii::$app->user->id;
	  $conn = Yii::$app->db;
	  $q = "SELECT COUNT(id) FROM userrole WHERE userid='$id' AND rid IN(0,2)";
	  $role = $conn->createCommand($q)->queryScalar();
	 if($role > 0)
	  {
	    return true;
	  }
	  return false;
	}
	
	public static function isSales()
	{
	  $id = Yii::$app->user->id;
	  $conn = Yii::$app->db;
	  $q = "SELECT COUNT(id) FROM userrole WHERE userid='$id' AND rid IN(0,5)";
	  $role = $conn->createCommand($q)->queryScalar();
	 if($role > 0)
	  {
	    return true;
	  }
	  return false;
	}
	
	public static function isOperation()
	{
	  $id = Yii::$app->user->id;
	  $conn = Yii::$app->db;
	  $q = "SELECT COUNT(id) FROM userrole WHERE userid='$id' AND rid IN(0,3)";
	  $role = $conn->createCommand($q)->queryScalar();
	 if($role > 0)
	  {
	    return true;
	  }
	  return false;
	}
	
	public static function isAccountant()
	{
	  $id = Yii::$app->user->id;
	  $conn = Yii::$app->db;
	  $q = "SELECT COUNT(id) FROM userrole WHERE userid='$id' AND rid IN(0,4)";
	  $role = $conn->createCommand($q)->queryScalar();
	 if($role > 0)
	  {
	    return true;
	  }
	  return false;
	}
	
	public static function isFirstTime($id)
	{
	  $conn = Yii::app()->db;
	  $q = "SELECT lcount FROM compuser WHERE id='$id'";
	  $role = $conn->createCommand($q)->queryScalar();
	  $conn->active = false;
	 if($role < 1)
	  {
	    return true;
	  }
	  return false;
	}
	
	
	
}
