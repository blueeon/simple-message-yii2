<?php
/**
 * author:      blueeon <blueeon@blueeon.net>
 * createTime:  20150520 23:04
 * fileName :   SigleInstance.php
 */

namespace blueeon\Message\components;
use yii\base\Exception;

/**
 * Class SingletonInstance    简单单例class基类
 *
 * @package common\components
 * @author  blueeon <blueeon@blueeon.net>
 */
abstract class SingletonInstance
{

    protected function __construct()
    {
    }

    /**
     * @var null 单例存放数组
     */
    protected static $instance = NULL;

    /**
     * 单例入口方法
     *
     * @static
     * @return mixed|null|static
     */
    public static function getInstance()
    {
        if (empty(static::$instance[get_called_class()])) {
            static::$instance[get_called_class()] = new static();
        }
        return static::$instance[get_called_class()];
    }

    /**
     * 禁止克隆
     *
     * @throws Exception
     *
     * @return void
     */
    final public function __clone()
    {
        throw new Exception('单例模式禁止克隆');
    }

    /**
     * 禁止串行化
     *
     * @throws Exception
     *
     * @return void
     */
    final public function __wakeup()
    {
        throw new Exception('单例模式禁止串行化');
    }
} 