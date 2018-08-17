<?php

namespace app\models;

use Yii;

class Invoiceitem extends \yii\db\ActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Invoiceitem the static model class
	 */
	 public $rdvat;
	 public $descr;
	
	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return 'invoiceitem';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			[['iid'], 'required'],
			
			[['rdvat'], 'safe'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'invoicenum' => 'Invoicenum',
			'iid' => 'Container #',
			'feeid' => 'Feeid',
			'amount' => 'Agency Fee',
			'quantity' => 'Quantity',
			'descr' => 'Description',
			'regby' => 'Regby',
			'regdate' => 'Regdate',
			'paidin' => 'Currency',
			'updates' => 'Updates',
		];
	}

	
}