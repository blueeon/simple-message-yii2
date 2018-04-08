<?php
// test database! Important not to run tests on production or development databases
$db = [
    'class'    => 'yii\db\Connection',
    'dsn'      => 'mysql:host=localhost;dbname=simple-message-yii2',
    'username' => 'root',
    'password' => '123',
    'charset'  => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

return $db;
