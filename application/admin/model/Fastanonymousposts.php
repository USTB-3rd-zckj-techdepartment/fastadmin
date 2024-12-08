<?php
namespace app\admin\model;

use think\Model;

class Fastanonymousposts extends Model
{
    // 指定数据表名称
    protected $name = 'fast_anonymous_posts';
    
    // 开启自动时间戳
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加额外的属性
    protected $append = [
        'status_text',
        'create_time_text',
        'update_time_text',
        'user_nickname'  // 添加关联用户昵称
    ];

    // 定义与用户模型的关联关系
    public function user()
    {
        return $this->belongsTo('Fastanonymoususer', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    // 获取状态列表
    public function getStatusList()
    {
        return ['normal' => '正常', 'hidden' => '禁用'];
    }

    // 状态文本转换
    public function getStatusTextAttr($value, $data)
    {
        $status = $data['status'] ?? 'normal';
        $list = $this->getStatusList();
        return $list[$status] ?? '';
    }

    // 获取关联用户的昵称
    public function getUserNicknameAttr($value, $data)
    {
        $user = $this->user;
        return $user ? $user->nickname : '';
    }

    // 格式化创建时间
    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $data['createtime'] ?? '';
        return $value ? date("Y-m-d H:i:s", $value) : '';
    }

    // 格式化更新时间
    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $data['updatetime'] ?? '';
        return $value ? date("Y-m-d H:i:s", $value) : '';
    }
}