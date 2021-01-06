<?php

namespace app\api\model;

use think\Model;
use think\Request;

class VersionModel extends CommonModel
{
    protected $table = 'jytx_version';
    protected $autoWriteTimeStamp = true;
}
