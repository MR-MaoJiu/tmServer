<?php


namespace app\admin\validate;


use think\Validate;

class AnnouncementValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require',
        'body' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '公告名必填',
        'body.require' => '公告内容必填',
    ];
}
