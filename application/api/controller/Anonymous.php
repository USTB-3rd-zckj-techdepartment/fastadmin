<?php
namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\Exception;
use think\Validate;

class Anonymous extends Api
{
    protected $noNeedLogin = ['login', 'register'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 登录接口
     */
    public function login()
    {
        $nickname = $this->request->post('nickname');
        $password = $this->request->post('password');
        
        $rule = [
            'nickname' => 'require|length:2,50',
            'password' => 'require|length:6,30'
        ];
        
        $data = [
            'nickname' => $nickname,
            'password' => $password
        ];
        
        $validate = new Validate($rule);
        $result = $validate->check($data);
        
        if (!$result) {
            $this->error($validate->getError());
        }
        
        $user = Db::name('anonymous_user')
            ->where('nickname', $nickname)
            ->where('status', 'normal')
            ->find();
            
        if (!$user) {
            $this->error('用户不存在');
        }
        
        if (md5($password) !== $user['password']) {
            $this->error('密码错误');
        }
        
        // 生成token
        $token = $this->auth->getToken($user['id']);
        
        $data = [
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'token' => $token
        ];
        
        $this->success('登录成功', $data);
    }

    /**
     * 注册接口
     */
    public function register()
    {
        $nickname = $this->request->post('nickname');
        $password = $this->request->post('password');
        $repassword = $this->request->post('repassword');
        
        $rule = [
            'nickname' => 'require|length:2,50|unique:anonymous_user',
            'password' => 'require|length:6,30',
            'repassword' => 'require|confirm:password'
        ];
        
        $data = [
            'nickname' => $nickname,
            'password' => $password,
            'repassword' => $repassword
        ];
        
        $validate = new Validate($rule);
        $result = $validate->check($data);
        
        if (!$result) {
            $this->error($validate->getError());
        }
        
        $data = [
            'nickname' => $nickname,
            'password' => md5($password),
            'createtime' => time(),
            'status' => 'normal'
        ];
        
        Db::startTrans();
        try {
            $userid = Db::name('anonymous_user')->insertGetId($data);
            if (!$userid) {
                exception('注册失败');
            }
            
            // 生成token
            $token