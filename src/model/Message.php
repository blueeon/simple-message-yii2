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
 * @property integer $status
 * @property string  $message
 * @property string  $created_time
 */
class Message extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message', 'created_time'], 'required'],
            [['reply_id', 'from', 'to', 'status'], 'integer'],
            [['message'], 'string'],
            [['created_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'reply_id'     => 'Reply ID',
            'from'         => 'From',
            'to'           => 'To',
            'status'       => 'Status',
            'message'      => 'Message',
            'created_time' => 'Created Time',
        ];
    }
}