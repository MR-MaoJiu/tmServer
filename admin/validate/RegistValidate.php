<?php

namespace app\Admin\validate;

use think\Validate;

class RegistValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require',
        'password' => 'require',
//	    'email' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '用户名必填',
        'password.require' => '密码必填',
//        'email.require' => '邮箱必填',
    ];
}
