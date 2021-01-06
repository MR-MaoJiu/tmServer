<?php


namespace app\api\controller;


use app\admin\model\AnnouncementModel;
use app\admin\validate\AnnouncementValidate;
use think\Request;

class Announcement extends Common
{
    protected $db;
    protected $checktoken;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new AnnouncementValidate();
        $this->db = new AnnouncementModel();
        $this->checktoken = new Login();
    }


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getAnnouncement(Request $request)
    {
        $token = $request->header('token') ? $request->header('token') : '';
        if (!$token) return json(['code' => 0, 'msg' => '请登录']);
        $uid = $this->getUid($token);
        if ($uid) {
            $list = $this->db
                ->where('status', 1)
                ->field('name,body,create_time')
                ->select();
            return json(['code' => 1, 'message' => '获取成功', 'announcementData' => $list]);

        } else {
            return json(['code' => 0, 'message' => '账号异常']);
        }

    }

    /**
     * 解密token获取用户id
     * @param $data
     * @return mixed
     */
    public function getUid($token)
    {
        $info = $this->checktoken->checkToken($token);
        $info1 = json_decode($info->getContent(), true);
        $info2 = json_decode($info1, true);
        $uid = $info2['uid'];
        return $uid;
    }
}