<?php

namespace app\api\model;

use think\Model;

class LoginModel extends CommonModel
{
    protected $table = 'app_user';
    protected $autoWriteTimeStamp = true;
    protected $createTime = 'request_time';
}
