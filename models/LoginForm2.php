<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm2 extends Model
{
   public $password;
   public $cpassword;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
             [['password', 'cpassword'], 'required'],
             ['cpassword', 'compare','compareAttribute'=>'password','message'=>'Password Did not Match']
        ];
    }
	
	public function attributeLabels()
	{
		return [
			'password' => 'New Password',
			'cpassword' => 'Confirm Password',
		];
	}

}
