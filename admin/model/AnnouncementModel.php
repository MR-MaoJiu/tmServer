<?php


namespace app\admin\model;


class AnnouncementModel extends CommonModel
{
    protected $table = 'user_announcement';
    protected $id = 'announcementid';
    protected $autoWriteTimeStamp = true;
    protected $updateTime = 'last_login_time';
}
