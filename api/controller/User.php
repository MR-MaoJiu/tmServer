<?php

namespace app\api\controller;

use app\api\model\CardModel;
use app\api\model\CommentModel;
use app\api\model\JujiModel;
use app\api\model\MenuModel;
use app\api\model\RegistModel;
use app\api\model\UserModel;
use app\api\model\VideoModel;
use app\api\validate\UserCardValidate;
use app\api\validate\UserInfoValidate;
use think\Controller;
use think\Db;
use think\Request;
use think\Validate;

class User extends Common
{
    protected $db;
    protected $validate;
    protected $regist;
    protected $uinfovalidate;
    protected $checktoken;

    public function initialize()
    {
        parent::initialize();
        $this->db = new UserModel();
        $this->validate = new UserCardValidate();
        $this->regist = new RegistModel();
        $this->uinfovalidate = new UserInfoValidate();
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
     * 个人中心
     * @param Request $request
     * @return \think\response\Json
     */
    public function userCenter(Request $request)
    {
        if ($request->isGet()) {
            $token = $request->header('token') ? $request->header('token') : '';
            $uid = $this->getUid($token);
            $user_info = UserModel::where(['u.status' => 1, 'u.uid' => $uid])
                ->alias('u')
                ->join('user_resources r', ' u.uid=r.uid', 'left')
                ->field('u.nickname,u.phone,u.age,u.onlinestatus,u.gander,r.src')
                ->find();
        } else {
            return json(['code' => 400, 'msg' => '请求不合法']);
        }
        return json(['code' => '1', 'userData' => $user_info]);
    }


    /**
     * 发送手机验证码
     * @param Request $request
     * @return \think\response\Json
     */
    public function getCode(Request $request)
    {
        if ($request->isPost()) {
            $validate = new Validate([
                'phone' => 'require|mobile'
            ]);
            $validate->message([
                'phone.require' => '手机号不能为空',
                'phone.mobile' => '手机号格式不正确',
            ]);
            $data = $request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            return $this->send_sms($data['phone']);
        } else {
            return json(['code' => 400, 'msg' => '请求不合法']);
        }
    }

    /**
     * 修改密码
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function editPass(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            if (!$data['phone']) return json(['code' => 0, 'msg' => '缺少参数']);
            $token = $request->header('token');
            $uid = $this->getUid($token);
            if ($data['mobile_code'] != session('mobile_code')) {
                return json(['code' => '400', 'msg' => '验证码错误请重新输入']);
            }
            $res = UserModel::where('id', $uid)->update(['password' => md5($data['password'])]);
            return $this->Res($res);
        } else {
            return json(['code' => 400, 'msg' => '请求不合法']);
        }
    }

    /**
     * @return \think\response\Json
     * @throws \think\exception\PDOException
     */
    public function editUserInfo()
    {
        if ($this->request->isPost()) {
            $user = new UserModel();
            $token = $this->request->header('token');
            $uid = $this->getUid($token);
            $info = $this->db->where(['status' => 1, 'uid' => $uid])->find();
            if (!$info) return json(['code' => 0, 'msg' => '该用户不存在']);
            $data = $this->request->post();
            $user->startTrans();
            $res = $user->save($data, ['uid' => $uid]);
            if ($res) {
                $user->commit();
                return json(['code' => 1, 'msg' => '修改成功']);
            } else {
                $user->rollback();
                return json(['code' => 0, 'msg' => '网络错误']);
            }

        }
    }


    /**
     * @param Request $request
     * @return \think\response\Json
     */

    public function getUsers(Request $request)
    {
        if ($this->request->isPost()) {
            $token = $request->header('token') ? $request->header('token') : '';
            if (!$token) return json(['code' => 0, 'msg' => '请登录']);
            $data = $this->request->param();
            // $res = searchByLatAndLng($data['latitude'], $data['longitude'], 15.00);
            $lat = $data['latitude'];
            $lng = $data['longitude'];
            $radius = $data['radius'];
            $scope = $this->calcScope($lat, $lng, $radius);     // 调用范围计算函数，获取最大最小经纬度
            /** 查询经纬度在 $radius 范围内的电站的详细地址 */
            //$sql = 'SELECT nickname,gander,age,onlinestatus,likenum,signature,address FROM `app_user` WHERE `latitude` < ' . $scope['maxLat'] . ' and `latitude` > ' . $scope['minLat'] . ' and `longitude` < ' . $scope['maxLng'] . ' and `longitude` > ' . $scope['minLng'];
            $sql = 'SELECT nickname,gander,age,onlinestatus,likenum,signature,address,src FROM `app_user`  left join user_resources on app_user.uid=user_resources.uid WHERE `latitude` < ' . $scope['maxLat'] . ' and `latitude` > ' . $scope['minLat'] . ' and `longitude` < ' . $scope['maxLng'] . ' and `longitude` > ' . $scope['minLng'];
            $stmt = $this->db->query($sql);

            return json(['code' => 1, 'message' => '获取成功', 'postData' => $stmt]);
        }

    }


    /**
     * 根据经纬度和半径计算出范围
     * @param string $lat 纬度
     * @param String $lng 经度
     * @param float $radius 半径
     * @return Array 范围数组
     */
    private function calcScope($lat, $lng, $radius)
    {
        $degree = (24901 * 1609) / 360.0;
        $dpmLat = 1 / $degree;

        $radiusLat = $dpmLat * $radius;
        $minLat = $lat - $radiusLat;       // 最小纬度
        $maxLat = $lat + $radiusLat;       // 最大纬度

        $mpdLng = $degree * cos($lat * (M_PI / 180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng * $radius;
        $minLng = $lng - $radiusLng;      // 最小经度
        $maxLng = $lng + $radiusLng;      // 最大经度

        /** 返回范围数组 */
        $scope = array(
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLng' => $minLng,
            'maxLng' => $maxLng
        );
        return $scope;
    }

}
