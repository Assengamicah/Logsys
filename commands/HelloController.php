<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;
use Yii;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";
    }
	
	public function actionTest()
    {
        echo 'Test Babie' . "\n";
    }
	public function actionMail()
	{
		$q ="SELECT oi.cno,oi.iid,DATE_FORMAT(oi.cdate,'%d/%m/%Y'),c.name,DATEDIFF(NOW(),oi.cdate) FROM orderitems oi  ";
		$q .="INNER JOIN itemgroup i ON i.gid = oi.gid INNER JOIN orders o ON o.orderno = oi.orderno INNER JOIN clients c ON  ";
		$q .="c.cid = o.cid WHERE oi.shipped = 'N' AND DATEDIFF(NOW(),oi.cdate) > 0";
		
		$rst = Yii::$app->db->createCommand($q)->queryAll(false);
		  foreach($rst as $rs)
		  {
			  $rst2 = Yii::$app->db->createCommand("SELECT name FROM items WHERE iid IN($rs[1])")->queryAll(false);
				 $d = '';
					foreach($rst2 as $r)
						{
						 $d .= $r[0].' , ';
					    }
				$it =  rtrim($d,' ,');
				$subject = 'Overdue Cargo With Control Number '.$rs[0];
				$message = "Dear <b>Goodie</b><br />";
				$message .= "Be informed that Cargo with control number:b>$rs[0]</b> which contains <b>$it</b> belongs to <b>$rs[3]";
				$message .= "</b> Has been stayed for <b>$rs[4] days</b> without being shipped.<br />";
				$message .= "=============================================";
				$qi ="INSERT INTO communications(sentto,subject,descr,cdate) VALUES('gmsangi@gmail.com','$subject','$message',";
				$qi .="NOW())";
				Yii::$app->db->createCommand($qi)->execute();
				
		  }
	}
	
	public function actionSend()
	{
		$rst = Yii::$app->db->createCommand("SELECT id,sentto,subject,descr FROM communications WHERE sent = 'N'")->queryAll(false);
		  foreach($rst as $rs)
		  {
			  $mailsend=mail("$rs[1]","$rs[2]","$rs[3]");
			  Yii::$app->db->createCommand("UPDATE communications SET sent = 'Y',datesent = NOW() WHERE id = '$rs[0]'")->execute();
		  }
	}
}
