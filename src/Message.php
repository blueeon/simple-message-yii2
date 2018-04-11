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
    /**
     * @var int username cache time
     */
    public $userNameCacheTime = 60 * 10;

    public function init()
    {
        parent::init();
        if (is_null($this->slave)) {
            $this->slave = $this->db;
        }
        // custom initialization code goes here
    }

    /**
     * send a message to one user
     *
     * @param int        $userId  接收者用户ID
     * @param string     $message 消息
     * @param null|array $from    发送者ID,默认当前用户
     * @return bool
     */
    public function send($userId, $message, $from = null)
    {
        if (!isset(\Yii::$app->user) && is_null($from)) {

            throw new \Exception('Param $from is needed.');
        } elseif (isset(\Yii::$app->user)) {
            $from = \Yii::$app->user->id;
        }

        return true;
    }

    /**
     * Reply a message.
     *
     * @param int    $messageId 消息ID
     * @param string $message   消息
     * @return bool
     */
    public function reply($messageId, $message)
    {
        return true;
    }

    /**
     * 获取某个用户的私信列表,按对话返回,且返回最新的一条回复
     *
     * @param int $userId
     * @param int $page
     * @param int $pageNum
     * @return array
     */
    public function messageList($userId, $page = 1, $pageNum = 30)
    {
        return [];
    }

    /**
     * 删除一条消息
     *
     * @param int $messageId
     * @return bool
     */
    public function del($messageId)
    {
        return true;
    }
}