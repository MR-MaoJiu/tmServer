<?php


namespace app\api\controller;


use app\api\model\ResourceModel;
use app\api\validate\ResourceValidate;
use think\Controller;
use think\Request;

class Resource extends Controller
{
    protected $db;
    protected $validate;
    protected $checktoken;

    public function initialize()
    {
        parent::initialize();
        $this->db = new ResourceModel();
        $this->validate = new  ResourceValidate();
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
     * 上传资源
     * @param Request $request
     * @return \think\response\Json
     */
    public function save(Request $request)
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
                $rid = $this->db->getLastInsID();
                return json(['code' => 1, 'message' => '操作成功', 'id' => $rid]);
            } else {
                $this->db->rollback();
                return json(['code' => 0, 'message' => '操作失败']);
            }
        }
    }

}