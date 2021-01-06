<?php

namespace app\api\validate;

use think\Validate;

class RegistValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'phone' => 'require',
	    'password' => 'require',
//	    'email' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'phone.require' => '用户名不能为空',
        'password.require' => '密码不能为空',
//        'email.require' => '邮箱必填',
    ];
}
