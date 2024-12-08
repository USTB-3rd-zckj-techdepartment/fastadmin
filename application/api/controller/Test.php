<?php

namespace app\api\controller;

use app\common\controller\Api;

class Test extends Api
{
    // 无需登录的接口
    protected $noNeedLogin = ['index'];
    // 无需鉴权的接口
    protected $noNeedRight = ['*'];

    /**
     * 测试接口
     */
    public function index()
    {
        $data = [
            'code' => 1,
            'msg'  => 'Hello World',
            'time' => date('Y-m-d H:i:s')
        ];
        return json($data);
    }
}