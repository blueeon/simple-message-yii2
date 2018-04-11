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
            'class' => \blueeon\Message\Message::className(),
            'db'    => 'db',
            'slave' => 'slave_db',
            //need to complete a function to get user's nickname.
//            'getUserName' => function ($userId) {
//                return $userId;
//            }
        ]);
    }

    protected function _after()
    {
    }

    // testsCreate
    public function testCreateComponent()
    {
        $obj    = Yii::createObject([
            'class'       => \blueeon\Message\Message::className(),
            'db'          => 'db',
            //need to complete a function to get user's nickname.
            'getUserName' => function ($userId, $cacheTime = 3600) {
                return $userId;
            }
        ]);
        $userId = rand(1, 100);
        $this->assertEquals(call_user_func($obj->getUserName, $userId), $userId);
    }

    // tests
    public function testSend()
    {
        $this->assertTrue($this->obj->send(1, 'How are u?', 2));
    }

    public function testReply()
    {
        $this->assertTrue($this->obj->reply(12, 'Fine thx,and u?'));
    }
}