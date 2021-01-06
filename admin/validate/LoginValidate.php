<?php

namespace app\admin\validate;

use think\Validate;

class LoginValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'name' =>'require',
	    'password' =>'require',
	    'captcha' =>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require' => '用户名或密码错误',
        'password.require' => '用户名或密码错误',
        'captcha.require' => '验证码必填',
    ];
}
