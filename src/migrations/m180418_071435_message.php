<?php

use yii\db\Migration;
use yii\db\Schema;

class m180418_071435_message extends Migration
{
    public function up()
    {
        $tableOptions = '';

        if (Yii::$app->db->driverName == 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('message', [
            'id'            => Schema::TYPE_PK,
            'reply_id'      => Schema::TYPE_INTEGER,
            'from'          => Schema::TYPE_INTEGER,
            'to'            => Schema::TYPE_INTEGER,
            'dialogue_hash' => Schema::TYPE_STRING . '(64) NOT NULL',
            'status'        => Schema::TYPE_INTEGER,
            'message'       => Schema::TYPE_TEXT,
            'created_time'  => Schema::TYPE_TIMESTAMP . ' NULL',
        ], $tableOptions);
        $this->addPrimaryKey('message-pk', 'message', ['id']);
        $this->createIndex('dialogue_hash', 'message', ['dialogue_hash']);
        $this->createIndex('from-to', 'message', ['from', 'to']);
        $this->createIndex('to', 'message', ['to']);
        $this->createIndex('reply_id', 'message', ['reply_id']);
    }

    public function down()
    {
        $this->dropTable('message');

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
