<?php

namespace app\api\validate;

use think\Validate;

class PostsValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'longitude' => 'require',
        'latitude' => 'require',
        'body' => 'require',
//	    'captcha' =>'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'longitude.require' => '经度必填',
        'latitude.require' => '纬度必填',
        'body.require' => '内容必填',
//        'captcha.require' => '验证码必填',
    ];
}
