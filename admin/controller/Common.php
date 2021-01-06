<?php

namespace app\admin\controller;

use app\admin\model\NodesModel;
use app\admin\model\UserModel;
use think\Controller;
use think\Db;
use think\facade\Request;

class Common extends Controller
{
    public function initialize()
    {
        parent::initialize();
        if (!session('uid')) {
            $this->redirect(url('Login/index'));
        }
//        $nodes = $this->checkNodes();
//        $url = '/'.request()->module() . '/' . request()->controller() . '/' . request()->action();
//        $url = strtolower($url);
//        if (isset($nodes['is_admin']) && $nodes['is_admin'] == 1){
//            return true;
//        }else{
//            if (!in_array($url,$nodes)){
//                $this->error('sorry权限不足');
//                exit();
//            }
//        }
    }
    /**
     * 无限分类查询
     * @param $data
     * @param int $pid
     * @return array
     */
    public function infinityCategory($data,$pid=0)
    {
        $ruleArr = [];
        foreach ($data as $k => $v) {
            if ($v['pid'] == $pid) {
                $v['lower'] = $this->infinityCategory($data,$v['id']);
                $ruleArr[] = $v;
                unset($data[$k]);
            }
        }
        return $ruleArr;
    }
    /**
     * 获取ip
     * @return bool|mixed
     */
     public static function get_real_ip()
    {
        $ip = FALSE;
        //客户端IP 或 NONE
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        //多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi("^(10│172.16│192.168).", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        //客户端IP 或 (最后一个)代理服务器 IP
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    /**
     * 删除成功返回结果
     * @param $res
     * @return \think\response\Json
     */
    public function delRes($res){
        if ($res){
            return json(['code'=>1,'message'=>'删除成功']);
        }else{
            return json(['code'=>0,'message'=>'删除失败']);
        }
    }

    /**
     * 添加成功返回结果
     * @param $res
     * @return \think\response\Json
     */
    public function addRes($res){
        if ($res){
            return json(['code'=>1,'message'=>'添加成功']);
        }else{
            return json(['code'=>0,'message'=>'添加失败']);
        }
    }

    /**
     * 修改成功返回结果
     * @param $res
     * @return \think\response\Json
     */
    public function editRes($res){
        if ($res){
            return json(['code'=>1,'message'=>'修改成功']);
        }else{
            return json(['code'=>0,'message'=>'修改失败']);
        }
    }

    /**
     * 修返回结果
     * @param $res
     * @return \think\response\Json
     */
    public function Res($res){
        if ($res){
            return json(['code'=>1,'message'=>'操作成功']);
        }else{
            return json(['code'=>0,'message'=>'操作失败']);
        }
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkNodes(){
        $uid = session('uid');
        $user_info = Db::name('admin_users')->where(['status'=>1,'id'=>$uid])->find();
        if (!$user_info) return "暂无此用户信息";
        if ($user_info['username'] == 'admin'){
            return ['is_admin'=>1];
        }else{
            $role_info = Db::name('roles')->where(['status'=>1])->where('id','in',explode(",",$user_info['rid']))->field('nid')->find();
            if (!$role_info) return "暂未分配角色";
            $node_info = Db::name('nodes')->where(['status'=>1])->where('id','in',explode(",",$role_info['nid']))->field('url')->select();
            if (!$node_info) return "暂未分配权限";
            $nodes = $node_info;
            if (!$nodes){
                return '暂无权限';
            }
        }
        $list = [];
        foreach ($nodes as $k=>$v){
            $list[$k] = $v['url'];
        }
        return $list;
    }

    /**
     * @param $str
     * @return bool
     */
    public static function checkstr($str)
    {
        $needle = ",";//判断是否包含a这个字符
        $tmparray = explode($needle, $str);
        if (count($tmparray) > 1) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $time 今天的时间戳
     */
    public function todayBegain($time){
        //当天开始时间
        $start_time=strtotime(date("Y-m-d",$time));
        //当天结束时间
        $end_time = $start_time+60*60*24;
        $today_time = [];
        $today_time['start'] = $start_time;
        $today_time['end'] = $end_time;
        return $today_time;
    }
}
