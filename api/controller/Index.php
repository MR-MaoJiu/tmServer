<?php
namespace app\api\controller;


use app\api\model\QuesstionModel;
use app\api\model\VersionModel;
use think\Db;

class Index extends Common
{
    /**
     * 版本更新
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getVersion(){
        $vsersion_data = VersionModel::where('status',1)->select();
        return json(['code'=>1,'msg'=>'操作成功','data'=>$vsersion_data]);
    }

    /**
     * 反馈问题
     * @return \think\response\Json
     */
    public function SubQuesstion(){
        if ($this->request->isPost()){
            if (!$this->request->header('token')) return json(['code'=>0,'msg'=>'请登录']);
            $data = $this->request->post();
            $quesstion = new QuesstionModel();
            $quesstion->startTrans();
            $res = $quesstion->save($data);
            if ($res){
                $quesstion->commit();
                return json(['code'=>1,'msg'=>'success']);
            }else{
                $quesstion->rollback();
                return json(['code'=>0,'msg'=>'error']);
            }
        }
    }

}
