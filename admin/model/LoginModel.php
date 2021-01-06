<?php

namespace app\admin\model;

use think\Model;

class LoginModel extends CommonModel
{
    protected $table = 'admin_user';
    protected $autoWriteTimeStamp = true;
}
