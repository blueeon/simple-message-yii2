<?php
/**
 * author:      blueeon <blueeon@blueeon.net>
 * createTime:  20180403 16:08
 * fileName :   Message.php
 */

namespace blueeon\Message;

use yii\base\Component;

/**
 * Class Message message create a application component to manage private message
 *
 * @package blueeon\Message
 * @author  Blueeon <blueeon@blueeon.net>
 */
class Message extends Component
{
    /**
     * @var \yii\db\Connection master DB connection
     *
     */
    public $db;
    /**
     * @var null|\yii\db\Connection slave DB connection, default to use $db
     *
     */
    public $slave = null;
    /**
     * @var function A anonymous method which is use to get user's nickname
     *               Will call it in loop.
     *
     */
    public $getUserName;

    public function init()
    {
        parent::init();
        if (is_null($this->slave)) {
            $this->slave = $this->db;
        }
        // custom initialization code goes here
    }
}