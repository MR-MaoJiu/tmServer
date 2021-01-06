<?php

namespace app\api\model;

use think\Model;

class UserModel extends CommonModel
{
    protected $table = 'app_user';
    protected $autoWriteTimeStamp = true;
}
