<?php

namespace app\admin\model;

use think\Model;

class HomeUserModel extends CommonModel
{
    //protected $table = 'app_user,user_image';
    protected $table = 'app_user';
    protected $name = 'users';
    protected $autoWriteTimeStamp = true;
    protected $updateTime = 'last_login_time';
}
