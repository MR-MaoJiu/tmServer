<?php

namespace app\api\model;

use think\Model;
use think\Request;

class VideoModel extends CommonModel
{
    protected $table = 'jytx_tv_video';
    protected $autoWriteTimeStamp = true;
}
