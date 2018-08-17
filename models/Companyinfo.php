<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "companyinfo".
 *
 * @property int $cid
 * @property string $cname
 * @property string $paddress
 * @property string $box
 * @property string $telephone
 * @property string $mob
 * @property string $fax
 * @property string $region
 * @property string $vat
 * @property string $tin
 * @property string $slogan
 * @property string $email
 * @property string $website
 */
class Companyinfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'companyinfo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cname', 'email', 'website'], 'string', 'max' => 100],
            [['paddress', 'slogan'], 'string', 'max' => 200],
            [['box', 'telephone', 'mob', 'fax', 'region', 'vat', 'tin'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cid' => 'Cid',
            'cname' => 'Cname',
            'paddress' => 'Paddress',
            'box' => 'Box',
            'telephone' => 'Telephone',
            'mob' => 'Mob',
            'fax' => 'Fax',
            'region' => 'Region',
            'vat' => 'Vat',
            'tin' => 'Tin',
            'slogan' => 'Slogan',
            'email' => 'Email',
            'website' => 'Website',
        ];
    }
}
