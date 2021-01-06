<?php

namespace app\admin\validate;

use think\Validate;

class VersionValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'name'  => 'require',
        'status'  => 'require',
        'desc'  => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'   => '版本名称必填',
        'status.require'  => '版本状态必填',
        'desc.require'  => '版本描述必填',
    ];
}
