<?php


namespace app\api\controller;


use app\api\model\CommentModel;
use app\api\validate\CommentValidate;
use think\Controller;
use think\Request;

class Comment extends Controller
{
    protected $db;
    protected $validate;
    protected $checktoken;

    public function initialize()
    {
        parent::initialize();
        $this->db = new CommentModel();
        $this->validate = new CommentValidate();
        $this->checktoken = new Login();
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

    /**
     * 发帖
     * @param Request $request
     * @return \think\response\Json
     */
    public function pushComment(Request $request)
    {
        if ($this->request->isPost()) {
            $token = $request->header('token') ? $request->header('token') : '';
            if (!$token) return json(['code' => 0, 'msg' => '请登录']);
            $uid = $this->getUid($token);
            $this->db->startTrans();
            $data = $this->request->post();
            if (!$this->validate->check($data)) {
                return json(['code' => 400, 'msg' => $this->validate->getError()]);
            }
            $data['uid'] = $uid;
            $data['create_time'] = time();
            $res = $this->db->_update($data);
            if ($res) {
                $this->db->commit();
                return json(['code' => 1, 'message' => '评论成功']);
            } else {
                $this->db->rollback();
                return json(['code' => 0, 'message' => '评论失败']);
            }
        }
    }

}
