<?php

namespace app\admin\model;

use think\Model;

class VideoModel extends CommonModel
{
    protected $table = 'jytx_tv_video';
    protected $name  = 'tv_video';
    protected $autoWriteTimestamp = true;
}
