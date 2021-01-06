<?php

namespace app\api\model;

use think\Model;

class RegistModel extends CommonModel
{
    protected $table = 'app_user';
    protected $autoWriteTimeStamp = true;
}
