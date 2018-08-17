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
class Inv extends Model
{
    public $invno;
   
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
           
            [['invno'], 'required'],
        ];
    }
	
	public function attributeLabels()
    {
        return [
            'invno' => 'Invoice Number',
            
            
        ];
    }


    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) //Make sure no errors as defined in rule
		{
            $user = $this->getUser();  //Get the user class

            if (!$user || !$user->validatePassword($this->password)) 
			{
                $this->addError($attribute, 'Login Failed. Incorrect username or password.');
            }
			elseif(!$user->isActive())
			{
				$this->addError($attribute, 'Your Account has been Blocked/Suspended.Please contact Admin for more information.');
			}
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) //validate all the rules before login, if OK Log me in
		{
			 return Yii::$app->user->login($this->getUser()); //Log this user in
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     * @return User|null
     */
    public function getUser()
    {
        if ($this->valid === false) 
		{
            $this->valid = User::findByUname($this->uname);  
        }

        return $this->valid;
    }
}
