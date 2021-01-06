<?php


namespace app\api\controller;


use app\api\model\PostsModel;
use app\api\model\UserModel;
use app\api\validate\PostsValidate;
use think\Controller;
use think\Exception;
use think\Request;
use function Sodium\add;

class Posts extends Controller
{
    protected $db;
    protected $validate;
    protected $checktoken;

    public function initialize()
    {
        parent::initialize();
        $this->db = new PostsModel();
        $this->validate = new PostsValidate();
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
    public function pushPost(Request $request)
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
                return json(['code' => 1, 'message' => '发帖成功']);
            } else {
                $this->db->rollback();
                return json(['code' => 0, 'message' => '发帖失败']);
            }
        }
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     */


    public function getPosts(Request $request)
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
            //查发帖信息
            $sql = 'SELECT user_post.body,user_post.likenum,user_post.create_time,user_resources.src,user_resources.type,user_post.uid FROM `user_post` left join user_resources on user_post.resourcesid=user_resources.id  WHERE user_post.status =1  and user_post.latitude < ' . $scope['maxLat'] . ' and user_post.latitude > ' . $scope['minLat'] . ' and user_post.longitude < ' . $scope['maxLng'] . ' and user_post.longitude > ' . $scope['minLng'];
            $stmt = $this->db->query($sql);
            $udb = new UserModel();
            foreach ($stmt as $item) {
                $user = $udb->where('u.uid', $item['uid'])
                    ->alias('u')
                    ->join('user_resources i', ' u.uid=i.uid', 'left')
                    ->field('u.nickname,u.age,u.gander,i.src')
                    ->find();
                $userinfo[] = $user;
            }


            //查用户信息
            return json(['code' => 1, 'message' => '获取成功', 'postData' => $stmt, 'PostUserData' => $userinfo]);
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
