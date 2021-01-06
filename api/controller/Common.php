<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class Common extends Controller
{
    public $log_file = './log.txt';
    public function initialize()
    {
        parent::initialize();
//        if (!$this->request->header('token') && $this->request->isPost()){
//            exit(json_encode(['code'=>0,'msg'=>'弄啥呢'],JSON_UNESCAPED_UNICODE));
//        }
        require_once ('config.ihuyi.php');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS,PATCH');
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
    //请求数据到短信接口，检查环境是否 开启 curl init。
    public function post($curlPost,$url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }
    //将 xml数据转换为数组格式。
    public function xml_to_array($xml){
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches)){
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++){
                $subxml= $matches[2][$i];
                $key = $matches[1][$i];
                if(preg_match( $reg, $subxml )){
                    $arr[$key] = $this-> xml_to_array( $subxml );
                }else{
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }
//random() 函数返回随机整数。
    public function random($length = 6 , $numeric = 0) {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }
    //防止恶意攻击
    public function sms_safe(){
        if($GLOBALS['ihuyi']['is_open_send_limit']!=1){
            return;
        }
        if (!empty($_SESSION['sms_send_black']) && $_SESSION['sms_send_black'] + $GLOBALS['ihuyi']['sms_send_black_time'] > time()) {
            exit('操作频繁,请'.ceil(($_SESSION['sms_send_black'] + $GLOBALS['ihuyi']['sms_send_black_time'] - time())/60).'分钟后重试');
        }

        if (empty($_SESSION['sms_send_num'])) {
            $_SESSION['sms_send_num'] = 1;
        }

        if(!empty($_SESSION['sms_send_time']) && $_SESSION['sms_send_time'] + $GLOBALS['ihuyi']['sms_send_time'] > time()){
            exit('操作频繁,请'.($_SESSION['sms_send_time'] + $GLOBALS['ihuyi']['sms_send_time'] - time()).'秒后重试');
        }

        if ($_SESSION['sms_send_num'] > $GLOBALS['ihuyi']['sms_send_num']) {
            $_SESSION['sms_send_black'] = time();
            unset($_SESSION['sms_send_num']);
            unset($_SESSION['sms_send_time']);
            exit('发送次数超过限制');
        }
    }
    //发送短信验证码
     public function send_sms($mobile){
        // 短信接口地址
        $target = $GLOBALS['ihuyi']['url'];
        //获取手机号
        $mobile = $mobile;
        //获取验证码
//        $send_code = md5($send_code);
        //生成的随机数
        $mobile_code = $this->random(4,1);
        if(empty($mobile)){
            exit('手机号码不能为空');
        }

        $preg = "/^1[3456789]\d{9}$/";
        if (!preg_match($preg, $mobile)) {
            exit('手机号码不正确');
        }

//        //校验图形验证码
//        if(empty($_SESSION['vcode']) or $send_code!=$_SESSION['vcode']){
//            exit('请输入正确验证码');
//        }

        //防止恶意攻击 session 部分代码最好通过redis代替session实现
        $this->sms_safe();
        $content = "您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。" ;
        $post_data = "account=".$GLOBALS['ihuyi']['appid'] ."&password=".$GLOBALS['ihuyi']['appkey'] ."&mobile=".$mobile."&content=".rawurlencode($content);
        $gets = $this-> xml_to_array($this->post($post_data, $target));

        if($gets['SubmitResult']['code']==2){
//            $_SESSION['mobile']			= $mobile;
//            $_SESSION['mobile_code']	= $mobile_code;
//            $_SESSION['sms_send_time']	= time();
//            $_SESSION['sms_send_num']	+= 1;
            redis()->set('sms_send_time',time());
           redis()->set('sms_send_exp',time()+60*10);
            redis()->set('mobile_code',$mobile_code);
            $data = date("Y-m-d H:i:s").' 返回码 : '. $gets['SubmitResult']['code'] .', 返回描述 : '.$gets['SubmitResult']['msg'].' . 发送号码 : '.$mobile.' , 短信详情 : '.$content.PHP_EOL;
            file_put_contents($this->log_file,$data,FILE_APPEND);
            return json(['code'=>1,'msg'=>'发送成功']);
        }else{
            $this->error($gets['SubmitResult']['msg']);
        }
//        echo $gets['SubmitResult']['msg'];
    }
    /**
     * @param $time 今天的时间戳
     */
    public function todayBegain($time){
        //当天开始时间
        $start_time=strtotime(date("Y-m-d",$time));
        //当天结束时间
        $end_time = $start_time+60*60*24;
        $today = [];
        $today['start'] = $start_time;
        $today['end'] = $end_time;
        return $today;
    }

}
