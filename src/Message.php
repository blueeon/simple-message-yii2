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
        $model                = new \blueeon\Message\models\Message();
        $model->from          = $fromUid;
        $model->to            = $toUid;
        $model->dialogue_hash = \blueeon\Message\models\Message::calHash($fromUid, $toUid);
        $model->message       = $message;
        $model->reply_id      = 0;
        $model->status        = \blueeon\Message\models\Message::$status['NORMAL'];
        $model->created_time  = date('Y-m-d H:i:s');
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
        $model                = new \blueeon\Message\models\Message();
        $model->from          = $messageModel->to;
        $model->to            = $messageModel->from;
        $model->dialogue_hash = \blueeon\Message\models\Message::calHash($model->from, $model->to);
        $model->message       = $message;
        $model->reply_id      = $messageId;
        $model->status        = \blueeon\Message\models\Message::$status['NORMAL'];
        $model->created_time  = date('Y-m-d H:i:s');
        if (!$model->save()) {
            throw new Exception('Save failed.', $model->getErrors(), 500);
        }
        return $model->attributes;
    }

    /**
     * 返回一条message的attributes
     *
     * @param $messageId
     * @return array
     * @throws \Exception
     */
    public function getMessage($messageId)
    {
        $model = \blueeon\Message\models\Message::findOne($messageId);
        if (empty($model)) {
            throw new \Exception('Can not find this message', 500);
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
        $return    = [];
        $countSql  = <<<EOF
SELECT count(DISTINCT dialogue_hash) as dialogue_amount 
FROM `message` 
WHERE `from` = :uid OR `to` = :uid AND status = 0
EOF;
        $slave     = $this->slave;
        $ret       = \Yii::$app->$slave->createCommand($countSql, [
            ':uid' => $userId,
        ])->queryOne();
        $total     = $ret['dialogue_amount'];
        $totalPage = 1 + (int)($total / $pageNum);
        $ret       = [];
        if ($total > 0) {


            $sql  = <<<EOF
SELECT max(id) as id, dialogue_hash,count(1) as message_amount
FROM `message` 
WHERE `from` = :uid OR `to` = :uid AND status = 0
GROUP BY dialogue_hash
ORDER BY created_time DESC
LIMIT :limit,:offset
EOF;
            $ret  = \Yii::$app->$slave->createCommand($sql, [
                ':uid'    => $userId,
                ':limit'  => ($page - 1) * $pageNum,
                ':offset' => $pageNum,
            ])->queryAll();
            $data = [];
            $ids  = [];
            foreach ($ret as $item) {
                $ids[]                        = $item['id'];
                $data[$item['dialogue_hash']] = $item;
            }
            $idStr = implode(',', $ids);
            $sql   = <<<EOF
SELECT dialogue_hash, reply_id, `from`, `to`, `message`, `created_time`
FROM `message`
WHERE id IN({$idStr})
EOF;
            $ret   = \Yii::$app->$slave->createCommand($sql)->queryAll();
            foreach ($ret as $item) {
                $data[$item['dialogue_hash']]['last_message'] = $item;
            }

        }
        $return = [
            'header' => [
                'total'        => $total,
                'totalPage'    => $totalPage,
                'current_page' => $page,
                'page_num'     => $pageNum,
            ],
            'data'   => $data,
        ];
        return $return;
    }

    /**
     * 删除一条消息
     *
     * @param int $messageId
     * @return bool
     */
    public function del($messageId)
    {
        return \blueeon\Message\models\Message::deleteAll([
            'id' => $messageId,
        ]);
    }

    /**
     * 删除一组对话
     *
     * @param $from
     * @param $to
     * @return int
     */
    public function delDialogue($from, $to)
    {
        $hash = \blueeon\Message\models\Message::calHash($from, $to);
        return \blueeon\Message\models\Message::deleteAll([
            'dialogue_hash' => $hash,
        ]);
    }
}