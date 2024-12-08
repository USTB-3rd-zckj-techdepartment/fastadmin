<?php
namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;
use think\Exception;
use think\Validate;

class Index extends Frontend
{
    protected $noNeedLogin = ['login', 'register'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        if ($this->auth->isLogin()) {
            $this->redirect('index/home');
        }
        return $this->view->fetch('login');
    }

    public function login()
    {
        if ($this->request->isPost()) {
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
            
            $this->auth->direct($user['id']);
            $this->success('登录成功', 'index/home');
        }
        return $this->view->fetch();
    }

    public function register()
    {
        if ($this->request->isPost()) {
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
            
            try {
                $userid = Db::name('anonymous_user')->insertGetId($data);
                if ($userid) {
                    $this->success('注册成功', 'index/login');
                } else {
                    $this->error('注册失败');
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
        return $this->view->fetch();
    }

    public function home()
    {
        if (!$this->auth->isLogin()) {
            $this->redirect('index/login');
        }
        
        $user = $this->auth->getUser();
        $posts = $this->getPosts();
        
        $this->view->assign([
            'user' => $user,
            'posts' => $posts['data']
        ]);
        return $this->view->fetch();
    }

    public function logout()
    {
        $this->auth->logout();
        $this->success('退出成功', 'index/login');
    }

     public function createPost()
    {
        // 添加详细的日志记录来帮助调试
        \think\Log::write('开始处理发帖请求');
        
        if (!$this->request->isPost()) {
            \think\Log::write('非POST请求');
            return json(['code' => 0, 'msg' => '非法请求方式']);
        }

        try {
            // 获取并验证用户输入
            $content = $this->request->post('content');
            if (empty($content)) {
                \think\Log::write('内容为空');
                return json(['code' => 0, 'msg' => '内容不能为空']);
            }

            // 获取当前登录用户
            $user = $this->auth->getUser();
            if (!$user) {
                \think\Log::write('用户未登录');
                return json(['code' => 0, 'msg' => '请先登录']);
            }

            // 记录用户信息
            \think\Log::write('当前用户ID: ' . $user->id);

            // 准备插入数据
            $data = [
                'user_id' => (int)$user->id, // 确保是整数
                'content' => strval($content), // 确保是字符串
                'createtime' => time(),
                'updatetime' => time(),
                'status' => 'normal',
                'likes' => 0,
                'comments' => 0
            ];

            // 记录准备插入的数据
            \think\Log::write('准备插入数据: ' . json_encode($data));

            // 插入帖子数据
            $postId = Db::name('anonymous_posts')->insertGetId($data);
            
            if (!$postId) {
                \think\Log::write('插入数据失败');
                return json(['code' => 0, 'msg' => '发布失败']);
            }

            // 查询新插入的帖子完整信息
            $post = Db::name('anonymous_posts')
                ->alias('p')
                ->join('anonymous_user u', 'p.user_id = u.id')
                ->field([
                    'p.id',
                    'p.content',
                    'p.createtime',
                    'p.likes',
                    'p.comments',
                    'u.nickname',
                    'u.avatar'
                ])
                ->where('p.id', $postId)
                ->find();

            if (!$post) {
                \think\Log::write('查询新帖子数据失败');
                return json(['code' => 0, 'msg' => '发布成功但获取数据失败']);
            }

            // 格式化返回数据
            $responseData = [
                'id' => (int)$post['id'],
                'content' => strval($post['content']),
                'nickname' => strval($post['nickname']),
                'avatar' => $post['avatar'] ? strval($post['avatar']) : '',
                'createtime' => (int)$post['createtime'],
                'likes' => (int)$post['likes'],
                'comments' => (int)$post['comments']
            ];

            \think\Log::write('发布成功，返回数据: ' . json_encode($responseData));
            return json(['code' => 1, 'msg' => '发布成功', 'data' => $responseData]);

        } catch (\Exception $e) {
            // 记录详细错误信息
            \think\Log::write('发布失败，错误信息: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return json(['code' => 0, 'msg' => '发布失败：' . $e->getMessage()]);
        }
    }

    public function getPosts()
    {
        $posts = Db::name('anonymous_posts')
            ->alias('p')
            ->join('anonymous_user u', 'p.user_id = u.id')
            ->where('p.status', 'normal')
            ->field('p.*, u.nickname, u.avatar')
            ->order('p.createtime desc')
            ->select();
            
        foreach ($posts as &$post) {
            $post['timeago'] = $this->timeago($post['createtime']);
        }
        
        return ['code' => 1, 'msg' => '获取成功', 'data' => $posts];
    }

    public function likePost()
    {
        if (!$this->auth->isLogin()) {
            $this->error('请先登录');
        }

        $post_id = $this->request->post('post_id');
        if (!$post_id) {
            $this->error('参数错误');
        }

        Db::name('anonymous_posts')
            ->where('id', $post_id)
            ->setInc('likes');
            
        $this->success('点赞成功');
    }

    private function timeago($timestamp) {
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return '刚刚';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . '天前';
        } else {
            return date('Y-m-d', $timestamp);
        }
    }
}