<?php

namespace app\api\validate;

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
	    'phone' =>'require',
	    'password' =>'require',
//	    'captcha' =>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'phone.require' => '手机号必填',
        'password.require' => '密码必填',
//        'captcha.require' => '验证码必填',
    ];
}
