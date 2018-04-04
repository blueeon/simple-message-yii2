<?php
/**
 * author:      BLueeon <blueeon@blueeon.net>
 * createTime:  20180404 10:36
 * fileName :   _bootstrap.php
 */
define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ .'/../vendor/autoload.php';