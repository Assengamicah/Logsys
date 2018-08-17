<?php

namespace app\models;

use Yii;
use yii\db\Expression;
use yii\helpers\Html; 

/**
 * This is the model class for table "roles".
 *
 * @property int $rid
 * @property string $name
 * @property int $cby
 * @property string $cdate
 * @property int $eby
 * @property string $edate
 */
class Roles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'roles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required',],
			[['name'], 'unique',],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rid' => 'Rid',
            'name' => 'Role Name',
            'cby' => 'Created By',
            'cdate' => 'Creation Date',
            'eby' => 'Eby',
            'edate' => 'Edate',
        ];
    }
	
	public function Roles()
	{
		$query = "SELECT rid,name FROM roles";
		$ans = Yii::$app->db->createCommand($query)->queryAll(false);
		$tb = "<table width = '50%' border = '1' margin = '2'>";
		$tb .= "<tr bgcolor = '#bdc3c7'><th>SN</th><th>ROLE</th><th><center>ACTION</center></th></tr>";
		$i=1;
		foreach($ans as $an)
		{
			$tb .= "<tr><td>$i</td><td>$an[1]</td><td><center><b>".Html::a('View', ['vrole','rid'=>$an[0]])."</b> | <b>".Html::a('Update', ['uprole','rid'=>$an[0]])."</b></center></td></tr>";
			$i++;
		}
		$tb .="</table>";
		
		return $tb;
	}
	
	public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) 
			{
                $this->cby = Yii::$app->user->id;
				$this->cdate = new Expression('NOW()');
            }
			else
			{
				$this->eby = Yii::$app->user->id;
				$this->edate = new Expression('NOW()');
			}
            return true;
        }
        return false;
    }
	
	public function Emp($empid)
	{
		$q = "SELECT CONCAT(fname,' ',IFNULL(mname,''),' ',sname) FROM employees WHERE empid = '$empid' ";
		$emp = Yii::$app->db->createCommand($q)->queryScalar();
		return $emp;
	}
	
	public function format1($datetime)
	{
		$date = explode(' ',$datetime);
		$dt = explode('-',$date[0]);
		$datetime = $dt[2].'/'.$dt[1].'/'.$dt[0];
		return $datetime;
	}
}
