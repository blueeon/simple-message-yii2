<?php

/**
 * author:      Blueeon <blueeon@blueeon.net>
 * createTime:  20180416 15:49
 * fileName :   Message.php
 */

namespace blueeon\Message\models;

use Yii;

/**
 * This is the model class for table "xm_message".
 *
 * @property integer $id
 * @property integer $reply_id
 * @property integer $from
 * @property integer $to
 * @property string  dialogue_hash
 * @property integer $status
 * @property string  $message
 * @property string  $created_time
 */
class Message extends \yii\db\ActiveRecord
{
    public static $status = [
        'NORMAL'  => '0',
        'DELETED' => '10',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message', 'created_time'], 'required'],
            [['reply_id', 'from', 'to', 'status'], 'integer'],
            [['message', 'dialogue_hash'], 'string'],
            [['created_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'reply_id'      => 'Reply ID',
            'from'          => 'From',
            'to'            => 'To',
            'dialogue_hash' => 'Dialogue Hash',
            'status'        => 'Status',
            'message'       => 'Message',
            'created_time'  => 'Created Time',
        ];
    }

    /**
     * 根据一组对话ID,排序后,返回唯一hash值
     *
     * @static
     * @param int $from
     * @param int $to
     * @return string
     */
    public static function calHash($from, $to)
    {
        $sort = [(int)$from, (int)$to];
        sort($sort);
        return md5(implode(',', $sort));
    }
}