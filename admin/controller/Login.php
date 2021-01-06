<?php

namespace app\admin\controller;

use app\admin\model\LoginModel;
use app\admin\model\RegistModel;
use app\admin\validate\LoginValidate;
use app\admin\validate\RegistValidate;
use Firebase\JWT\JWT;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Request;

class Login extends Controller
{
    protected $validate;
    protected $db;
    protected $rvalidate;
    protected $regist;

    public function __construct()
    {
        parent::__construct();
        $this->validate = new LoginValidate();
        $this->db = new LoginModel();
        $this->rvalidate = new RegistValidate();
        $this->regist = new RegistModel();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$this->validate->check($data)) {
                return json(['code' => 400, 'msg' => $this->validate->getError()], 400);
            }
            $where = [];
            $where['status'] = 1;
            $where['name'] = $data['name'];
            $where['password'] = md5($data['password']);
            $info = Db::name('admin_user')->where($where)->find();
            if (!$info) {
                return json(['code' => 400, 'msg' => '用户名或密码错误']);
            }
            //状态不是1
            if ($info && $info['status'] != 1) {
                return json(['code' => 400, 'msg' => '用户状态异常请联系管理员']);
            }
            if (!captcha_check($data['captcha'])) {
                return json(['code' => 400, 'msg' => '验证码错误']);
            }
            session('uid', $info['id']);
            //检查权限
            $update['id'] = $info['id'];
            $update['last_login_time'] = time();
            $update['ip'] = Common::get_real_ip();
            $update['login_count'] = $info['login_count'] + 1;
            $this->db->_update($update);
            return json(['code' => 1, 'msg' => '登录成功']);
        } else {
            return json(['code' => 401, 'msg' => '请求不合法',], 400);
        }

    }

    /**
     * @return mixed
     */
    public function regist()
    {
        return $this->fetch();
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     */
    public function doregist(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            if (!$this->rvalidate->check($data)) {
                return json(['code' => 400, 'msg' => $this->rvalidate->getError()]);
            }
            $info = $this->db->where('name', $data['name'])->find();
            if ($info) {
                return json(['code' => 400, 'msg' => '用户名已存在']);
            }
            $data['password'] = md5($data['password']);
            $res = $this->regist->_update($data);
            if ($res) {
                return json(['code' => 1, 'msg' => '注册成功']);
            } else {
                return json(['code' => 0, 'msg' => '网络错误']);
            }

        } else {
            return json(['code' => 400, 'msg' => '请求不合法']);
        }

    }

    /**
     * 生成验证码
     * @return mixed
     */
    public function verify()
    {
        $config = [
            // 验证码字体大小
            'fontSize' => 20,
            // 验证码位数
            'length' => 6,
            // 关闭验证码杂点
            'useNoise' => false,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

    /**
     * @return \think\response\Redirect
     */
    public function logout()
    {
        session('uid', null);
        return json(['code' => 1, 'msg' => '退出成功']);
    }
}
