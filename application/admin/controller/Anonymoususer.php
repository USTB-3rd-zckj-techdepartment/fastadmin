<?php
namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 匿名用户管理
 *
 * @icon fa fa-user-secret
 */
class Anonymoususer extends Backend
{
    // 设置模型变量
    protected $model = null;
    
    public function _initialize()
    {
        // 调用父类的初始化方法
        parent::_initialize();
        
        // 实例化模型
        $this->model = new \app\admin\model\Anonymoususer;
        
        // 设置状态列表,供模板使用
        $this->view->assign("statusList", [
            'normal' => '正常',
            'hidden' => '隐藏'
        ]);
    }
    
    /**
     * 查看列表
     */
    public function index()
    {
        // 判断是否为AJAX请求
        if ($this->request->isAjax()) {
            // 获取过滤条件、排序等参数
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            
            // 查询数据列表
            $list = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            
            // 处理时间格式
            foreach ($list as &$row) {
                $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
            }
            
            // 获取总记录数
            $total = $this->model->where($where)->count();
            
            // 返回JSON数据
            return json(['total' => $total, 'rows' => $list]);
        }
        // 显示列表页面
        return $this->view->fetch();
    }
}