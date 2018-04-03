<?php
/**
 * author:      blueeon <blueeon@blueeon.net>
 * createTime:  20180403 16:12
 * fileName :   DefaultController.php
 */

namespace blueeon\Message\controllers;

use blueeon\Message\components\API;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        API::getInstance()->renderJson('ok', 'Hello World!');
    }
}