<?php
/**
 * author:      YangKe <yangke@xiaomi.com>
 * createTIme:  20180403 16:08
 * fileName :   Module.php
 */

namespace blueeon\Message;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'blueeon\Message\controllers';
    /**
     * @var \yii\db\Connection master DB connection
     *
     */
    public $dbMaster;
    /**
     * @var \yii\db\Connection slave DB connection
     *
     */
    public $dbSlave;


    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}