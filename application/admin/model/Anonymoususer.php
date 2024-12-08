<?php
namespace app\admin\model;

use think\Model;

class Fastanonymoususer extends Model
{
    // 指定数据表名称
    protected $name = 'anonymous_user';
    
    // 开启自动时间戳
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    // updatetime 字段在此表中不存在，所以不定义 updateTime
    
    // 追加额外的属性
    protected $append = [
        'status_text',
        'create_time_text'
    ];

    // 获取状态列表，用于后台筛选
    public function getStatusList()
    {
        return ['normal' => '正常', 'hidden' => '禁用'];
    }

    // 将状态值转换为可读文本
    public function getStatusTextAttr($value, $data)
    {
        $status = $data['status'] ?? 'normal';
        $list = $this->getStatusList();
        return $list[$status] ?? '';
    }

    // 格式化创建时间显示
    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $data['createtime'] ?? '';
        return $value ? date("Y-m-d H:i:s", $value) : '';
    }
}