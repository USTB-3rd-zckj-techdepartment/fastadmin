<?php
namespace app\admin\controller;

use app\common\controller\Backend;

class Fastanonymousposts extends Backend
{
    protected $model = null;
    protected $relationSearch = true;
    
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Anonymousposts;
        
        $this->view->assign("statusList", [
            'normal' => '正常',
            'hidden' => '隐藏'
        ]);
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            
            $list = $this->model
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
                    
            foreach ($list as &$row) {
                $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
                $row['updatetime'] = date('Y-m-d H:i:s', $row['updatetime']);
            }
            
            $total = $this->model->with(['user'])->where($where)->count();
            
            return json(['total' => $total, 'rows' => $list]);
        }
        return $this->view->fetch();
    }
}