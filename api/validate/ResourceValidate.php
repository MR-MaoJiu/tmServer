<?php


namespace app\api\validate;


use think\Validate;

class ResourceValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'usefulness' => 'require',
        'src' => 'require',
        'type' => 'require',
//	    'captcha' =>'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'usefulness.require' => '用处必填',
        'src.require' => '资源地址必填',
        'type.require' => '类型必填',
//        'captcha.require' => '验证码必填',
    ];
}
