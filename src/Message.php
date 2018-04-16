<?php
/**
 * author:      blueeon <blueeon@blueeon.net>
 * createTime:  20180403 16:08
 * fileName :   Message.php
 */

namespace blueeon\Message;

use yii\base\Component;
use yii\db\Exception;

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
     * @param int    $toUid   接收者用户ID
     * @param string $message 消息
     * @param null   $fromUid 发送者ID,默认当前用户
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function send($toUid, $message, $fromUid = null)
    {
        if (!isset(\Yii::$app->user) && is_null($fromUid)) {

            throw new \Exception('Param $from is needed.');
        } elseif (isset(\Yii::$app->user)) {
            $fromUid = \Yii::$app->user->id;
        }
        $model               = new \blueeon\Message\models\Message();
        $model->from         = $fromUid;
        $model->to           = $toUid;
        $model->message      = $message;
        $model->reply_id     = 0;
        $model->created_time = date('Y-m-d H:i:s');
        if (!$model->save()) {
            throw new Exception('Save failed.', $model->getErrors(), 500);
        }
        return $model->attributes;
    }

    /**
     * Reply a message.
     *
     * @param int    $messageId 消息ID
     * @param string $message   消息
     * @throws Exception
     * @throws \Exception
     */
    public function reply($messageId, $message)
    {

        $messageModel = \blueeon\Message\models\Message::findOne($messageId);
        if (empty($message)) {
            throw new \Exception('Can not find this message', 500);
        }
        $model               = new \blueeon\Message\models\Message();
        $model->from         = $messageModel->to;
        $model->to           = $messageModel->from;
        $model->message      = $message;
        $model->reply_id     = $messageId;
        $model->created_time = date('Y-m-d H:i:s');
        if (!$model->save()) {
            throw new Exception('Save failed.', $model->getErrors(), 500);
        }
        return $model->attributes;
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

        $sql = <<<EOF
SELECT from, reply_id, to, status, message, create_time
FROM {{%message}}
WHERE to = :to_uid
UNION
SELECT from, reply_id, to, status, message, create_time
FROM {{%message}}
WHERE from = :to_uid
EOF;
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