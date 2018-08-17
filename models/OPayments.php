<?php

namespace app\models;

use Yii;
use yii\base\Model;

class OPayments extends Model
{
	public $fid;
	public $amt;
	public $hasvat;
	public $paidin2;
	

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return [
	
			[['fid', 'amt','paidin2'], 'required'],
			['amt', 'number','min'=>5],
			// rememberMe needs to be a boolean
			
		];
	}
	
	public function attributeLabels()
	{
		return [
			'fid' => 'Charges / Fees For',
			'amt' => 'Amount',
			'hasvat' => 'Has VAT?',
            'paidin2'=>'Currency',
		];
	}

}
