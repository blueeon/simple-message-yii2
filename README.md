# simple-message-yii2
A simple private message extension for yii2, only contain model and API, no web interface.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist blueeon/simple-message-yii2 "*"
```

or add

```
"blueeon/simple-message-yii2": "*"
```

to the require section of your `composer.json` file.

Then

```
 php yii migrate/up --migrationPath=@vendor/blueeon/simple-message-yii2/src/migrations

```

Configuration
-----
To use this extension, simply add the following code in your application configuration: 
```php
return [
    //....
    'components' => [
        'message' => [
            'class' => 'blueeon\Message\Message',
            'db' => 'db',
            'slave' => 'slave_db',
            //need to complete a function to get user's nickname.
            'getUserName' => function($userId, $cacheTime=3600){
                //...
            }
        ],
    ],
];
```

Usage
-----

Once the extension is installed, simply use it in your code by :

```php
//send a new private message to one user.
$res = Yii::$app->message->send($userId = 12,$message = 'How are u?');

//replay a message.
$res = Yii::$app->message->reply($messageId = 2100,$message = 'Fine, and u?');

//list all of messages.
$res = Yii::$app->message->messageList($userId = 12, $page = 1, $pageNum = 30);

//delete a message
$res = Yii::$app->message->del($messageId = 2100);

//delete a dialogue
$res = Yii::$app->message->delDialogue($uidFrom,$uidTo);



```