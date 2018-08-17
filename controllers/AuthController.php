<?php
/**
 * Created by PhpStorm.
 * User: Roby
 * Date: 12/23/17
 * Time: 1:52 PM
 */

namespace app\controllers;

use yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AuthController extends Controller{


    protected $VIEW_PATH = 'auth/';


    public function actionLogin() {

        return $this->render($this->VIEW_PATH . 'login');
    }
    

    public function actionResetPassword() {

        return $this->render($this->VIEW_PATH . 'login');
    }


}