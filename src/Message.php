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
     * @var string cache
     */
    public $cache = 'cache';
    /**
     * @var string cache prefix
     */
    public $cachePrefix = 'blueeon\Message#';
    /**
     * @var function A anonymous method which is use to get user's nickname
     *               Will call it in loop.
     *
     */
    public $getUserName;

    /**
     * @var int username cache time, if value equal 0,disable cache
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
        if (is_null($fromUid) && !isset(\Yii::$app->user)) {

            throw new \Exception('Param $from is needed.');
        } elseif (is_null($fromUid) && isset(\Yii::$app->user)) {
            $fromUid = \Yii::$app->user->id;
        }
        $model                = new \blueeon\Message\models\Message();
        $model->from          = $fromUid;
        $model->to            = $toUid;
        $model->dialogue_hash = \blueeon\Message\models\Message::calHash($fromUid, $toUid);
        $model->message       = $message;
        $model->reply_id      = 0;
        $model->status        = \blueeon\Message\models\Message::$status['UNREAD'];
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
        $model->status        = \blueeon\Message\models\Message::$status['UNREAD'];
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
        $ret   = [];
        $model = \blueeon\Message\models\Message::findOne($messageId);
        if (empty($model)) {
            throw new \Exception('Can not find this message', 500);
        }
        $ret              = $model->attributes;
        $ret['from_name'] = $this->getUserName($model->from);
        $ret['to_name']   = $this->getUserName($model->to);
        return $ret;
    }

    /**
     * 获取某个用户的私信列表,按对话返回,且返回最新的一条回复
     *
     * @param int $userId
     * @param int $page
     * @param int $pageNum
     * @return array
     */
    public function messageList($userId, $page = 0, $pageNum = 30)
    {
        $return    = [];
        $countSql  = <<<EOF
SELECT count(DISTINCT dialogue_hash) as dialogue_amount 
FROM `message` 
WHERE `from` = :uid OR `to` = :uid AND status < 10
EOF;
        $slave     = $this->slave;
        $ret       = \Yii::$app->$slave->createCommand($countSql, [
            ':uid' => $userId,
        ])->queryOne();
        $total     = $ret['dialogue_amount'];
        $totalPage = 1 + (int)($total / $pageNum);
        $ret       = [];
        $data      = [];
        if ($total > 0) {
            //total
            $sql = <<<EOF
SELECT max(id) as id, dialogue_hash,count(1) as message_amount, IF(`from` = :uid , `to` ,`from`) as talker
FROM `message` 
WHERE `from` = :uid OR `to` = :uid AND status < 10
GROUP BY dialogue_hash
ORDER BY created_time DESC
LIMIT :limit,:offset
EOF;
            $ret = \Yii::$app->$slave->createCommand($sql, [
                ':uid'    => $userId,
                ':limit'  => ($page) * $pageNum,
                ':offset' => $pageNum,
            ])->queryAll();

            $ids      = [];
            $hashList = [];
            foreach ($ret as $item) {
                $ids[]                                       = $item['id'];
                $hashList[]                                  = "'{$item['dialogue_hash']}'";
                $data[$item['dialogue_hash']]                = $item;
                $data[$item['dialogue_hash']]['talker_name'] = $this->getUserName($item['talker']);
                $data[$item['dialogue_hash']]['unread']      = 0;
            }
            $idStr       = implode(',', $ids);
            $hashListStr = implode(',', $hashList);

            //============
            //unread
            $sql = <<<EOF
SELECT dialogue_hash, count(1) as unread
FROM `message`
WHERE dialogue_hash IN({$hashListStr}) AND status = :status
GROUP BY dialogue_hash
EOF;
            $ret = \Yii::$app->$slave->createCommand($sql, [
                ':status' => \blueeon\Message\models\Message::$status['UNREAD'],
            ])->queryAll();
            foreach ($ret as $item) {
                $data[$item['dialogue_hash']]['unread'] = $item['unread'];
            }
            //last message
            $sql = <<<EOF
SELECT dialogue_hash, reply_id, `from`, `to`, `message`, `created_time`
FROM `message`
WHERE id IN({$idStr})
EOF;
            $ret = \Yii::$app->$slave->createCommand($sql)->queryAll();
            foreach ($ret as $item) {
                $data[$item['dialogue_hash']]['last_message']              = $item;
                $data[$item['dialogue_hash']]['last_message']['from_name'] = $this->getUserName($item['from']);
                $data[$item['dialogue_hash']]['last_message']['to_name']   = $this->getUserName($item['to']);
            }
            //===========end

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

    /**
     * 设置消息已读状态
     *
     * @param $messageId
     * @return int
     */
    public function setMessageRead($messageId)
    {
        return \blueeon\Message\models\Message::updateAll([
            'status' => \blueeon\Message\models\Message::$status['READ'],
        ], 'id = :id', [
            ':id' => $messageId,
        ]);
    }

    /**
     * 设置对话已读状态
     *
     * @param $dialogueHash
     * @return int
     */
    public function setDialogueRead($dialogueHash)
    {
        return \blueeon\Message\models\Message::updateAll([
            'status' => \blueeon\Message\models\Message::$status['READ'],
        ], 'dialogue_hash = :dialogue_hash', [
            ':dialogue_hash' => $dialogueHash,
        ]);
    }

    private static $userName = [];

    /**
     * 返回用户ID对应的名称:先读静态变量,后读cache,再读用户指定函数
     *
     * @param int $userId
     * @return string|int
     */
    public function getUserName($userId)
    {
        if ($this->userNameCacheTime == 0) {
            $disableCache = true;
        } else {
            $disableCache = false;
        }
        $cache      = $this->cache;
        $cacheKey   = $this->cachePrefix . $userId;
        $cacheGroup = 'group';
        if ($disableCache || !isset(self::$userName[$cacheGroup][$userId])) {
            $cacheName = \Yii::$app->$cache->get($cacheKey);
            if ($disableCache || empty($cacheName)) {

                if (!is_null($this->getUserName)) {
                    $name = call_user_func($this->getUserName, $userId);
                } else {
                    $name = $userId;
                }
                \Yii::$app->$cache->set($cacheKey, $name, $this->userNameCacheTime);
                self::$userName[$cacheGroup][$userId] = $name;
            } else {
                self::$userName[$cacheGroup][$userId] = $cacheName;
            }
        }
        return self::$userName[$cacheGroup][$userId];
    }
}