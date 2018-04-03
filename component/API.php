<?php

/**
 * author:      blueeon <blueeon@blueeon.net>
 * createTime:  20180403 16:13
 * fileName :   API.php
 */

namespace blueeon\Message\component;

use blueeon\Message\components\SingletonInstance;

class API extends SingletonInstance
{
    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';

    /**
     * 渲染接口返回值为json
     *
     * @param string       $status
     * @param array|string $data
     */
    public function renderJson($status, $data)
    {
        if (!in_array($status, [self::STATUS_OK, self::STATUS_ERROR])
        ) {

            $return = [
                'header' => [
                    'code'   => 500,
                    'status' => self::STATUS_ERROR,
                ],
                'data'   => 'Invalid value for $status',
            ];
        } else {

            $return = [
                'header' => [
                    'code'   => $code,
                    'status' => $status,
                ],
                'data'   => $data,
            ];
        }
        echo json_encode($return);
        exit;
    }
}