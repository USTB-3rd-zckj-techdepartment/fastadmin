<?php
namespace app\common\model;

use think\Model;

class AnonymousUser extends Model
{
    protected $name = 'anonymous_user';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
}