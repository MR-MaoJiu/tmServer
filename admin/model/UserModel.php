<?php

namespace app\admin\model;

use think\Model;

class UserModel extends CommonModel
{
    protected $table = 'admin_user';
    protected $name = 'admin_user';
    protected $autoWriteTimeStamp = true;
    protected $updateTime = 'last_login_time';
}
