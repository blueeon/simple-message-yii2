<?php

class MessageTest extends \Codeception\Test\Unit
{
    /**
     * @var null|blueeon\Message\Message
     */
    private $obj = null;

    protected function _before()
    {
        $this->obj = Yii::createObject([
            'class'             => \blueeon\Message\Message::className(),
            'db'                => 'db',
            'getUserName'       => function ($userId) {
                return "USER#{$userId}";
            },
            'userNameCacheTime' => 5,
        ]);
    }

    protected function _after()
    {
    }

    /**
     * Test Component create
     */
    public function testCreateComponent()
    {
        $obj    = Yii::createObject([
            'class'       => \blueeon\Message\Message::className(),
            'db'          => 'db',
            'getUserName' => function ($userId) {
                return $userId;
            }
        ]);
        $userId = rand(1, 100);
        $this->assertEquals(call_user_func($obj->getUserName, $userId), $userId);
    }

    /**
     * Test function send()
     */
    public function testSend()
    {
        $msg = 'How are u?';
        $ret = $this->obj->send(1, 'How are u?', 2);
        $this->assertNotFalse($ret);
        $this->assertEquals($ret['message'], $msg);
        $this->assertEquals($ret['from'], 2);
        $this->assertEquals($ret['to'], 1);

        $this->obj->del($ret['id']);
    }

    /**
     * Test function reply()
     */
    public function testReply()
    {
        $ret   = $this->obj->send(1, 'How are u?', 2);
        $msg   = 'Fine thx,and u?';
        $reply = $this->obj->reply($ret['id'], $msg);
        $this->assertNotFalse($reply);
        $this->assertEquals($reply['message'], $msg);
        $this->assertEquals($reply['from'], $ret['to']);
        $this->assertEquals($reply['to'], $ret['from']);

        $this->obj->del($ret['id']);
        $this->obj->del($reply['id']);
    }

    /**
     * Test function send()
     */
    public function testGetMessage()
    {
        $msg     = 'How are u?';
        $ret     = $this->obj->send(1, 'How are u?', 2);
        $message = $this->obj->getMessage($ret['id']);

        $this->assertNotFalse($message);
        $this->assertEquals($message['message'], $msg);
        $this->assertEquals($message['from'], 2);
        $this->assertEquals($message['to'], 1);
        $this->obj->del($ret['id']);
    }

    /**
     * Test function messageList()
     */
    public function testMessageList()
    {
        $this->obj->delDialogue(1, 2);
        $this->obj->delDialogue(1, 3);
        $ret1 = $this->obj->send(1, 'How are u?', 2);
        $ret1 = $this->obj->reply($ret1['id'], 'Fine,and u?');
        $ret1 = $this->obj->reply($ret1['id'], 'Im fine too.');


        $ret2 = $this->obj->send(1, 'How are u?', 3);
        $ret2 = $this->obj->reply($ret2['id'], 'Fine,and u?');
        $ret2 = $this->obj->reply($ret2['id'], 'Im fine too.');

        $ret = $this->obj->messageList(1);

        $this->assertEquals($ret['header']['total'], 2);
        $this->assertEquals($ret['header']['totalPage'], 1);
        $this->assertEquals(count($ret['data']), 2);
        $this->assertEquals($ret['data'][$ret1['dialogue_hash']]['last_message']['message'], 'Im fine too.');
        $this->assertEquals($ret['data'][$ret1['dialogue_hash']]['last_message']['from'], 2);

        $this->obj->delDialogue(1, 2);
        $this->obj->delDialogue(1, 3);
    }

    /**
     * Test function dialogueMessageList()
     */
    public function testDialogueMessageList()
    {
        $this->obj->delDialogue(1, 2);
        $ret1 = $this->obj->send(1, 'How are u?', 2);
        $ret1 = $this->obj->reply($ret1['id'], 'Fine,and u?');
        $ret1 = $this->obj->reply($ret1['id'], 'Im fine too.');

        $ret = $this->obj->dialogueMessageList($ret1['dialogue_hash'], 1);
        $this->assertTrue(!empty($ret['header']['max_id']));
        $this->assertTrue(count($ret['data']) == 3);
        $this->assertTrue($ret['data'][0]['message'] == $ret1['message']);

        $this->obj->delDialogue(1, 2);
    }

    /**
     * Test function del()
     *
     * @return int
     */
    public function testDel()
    {
        $msg = 'How are u?';
        $ret = $this->obj->send(1, $msg, 2);
        $this->assertNotFalse($ret);
        $this->obj->del($ret['id']);

        try {
            $ret = $this->obj->getMessage($ret['id']);

        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), 'Can not find this message');
            return 1;
        }
        $this->assertFalse($ret);

    }

    /**
     * Test function delDialogue()
     *
     * @return int
     */
    public function testDelDialogue()
    {
        $msg = 'How are u?';
        $ret = $this->obj->send(1, $msg, 2);
        $this->obj->delDialogue($ret['from'], $ret['to']);

        $msg = 'How are u?';
        $ret = $this->obj->send(1, $msg, 2);
        $this->obj->delDialogue($ret['to'], $ret['from']);
        try {
            $ret = $this->obj->getMessage($ret['id']);

        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), 'Can not find this message');
            return 1;
        }
        $this->assertFalse($ret);

    }
}