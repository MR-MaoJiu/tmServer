<?php


namespace app\api\model;


class PostsModel extends CommonModel
{
    protected $table = 'user_post';
    protected $autoWriteTimestamp = true;
}
