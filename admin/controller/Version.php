<?php
namespace app\admin\controller;

use app\admin\model\VersionModel;
use app\admin\validate\VersionValidate;
use think\Controller;
use think\Db;
use think\Request;

class Version extends Common
{
    protected $validate;
    protected $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = new VersionModel();
        $this->validate = new VersionValidate();
    }

    public function index(){
        $data = $this->request->param();
        $data['status'] = '';
        $list = $this->db
            ->where(function($query)use($data){
                $search = isset($data['status'])?trim($data['status']):'';
                if($search !== ''){
                    $query->where('status',$search);
                }
            })
            ->where(function($query)use($data){
                $search = isset($data['name'])?trim($data['name']):'';
                if($search){
                    $query->where('name','like','%'.$search.'%');
                }
            })
            ->where(function($query)use($data){
                $search = isset($data['desc'])?trim($data['desc']):'';
                if($search){
                    $query->where('desc','like','%'.$search.'%');
                }
            })
            ->where(function($query)use($data){
                $search = isset($data['start'])?trim($data['start']):'';
                if($search){
                    $query->where('create_time','>=',strtotime($data['start']));
                }
            })
            ->where(function($query)use($data){
                $search = isset($data['end'])?trim($data['end']):'';
                if($search){
                    $query->where('create_time','<=',strtotime($data['end']));
                }
            })
            ->where(function($query)use($data){
                $search['start'] = isset($data['start'])?trim($data['start']):'';
                $search['end'] = isset($data['end'])?trim($data['end']):'';
                if($search['start'] && $search['end']){
                    $query->where('create_time','between',[strtotime($search['start']),strtotime($search['end'])]);
                }
            })
            ->order('id asc')->paginate(10);

        $this->assign('total',$list->count());
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * @return mixed
     */
    public function create(){
        return $this->fetch();
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     */
    public function save(){
        if ($this->request->isPost()){
            $this->db->startTrans();
            $data = $this->request->post();
            if (!$this->validate->check($data)){
                return json(['code'=>400,'msg'=>$this->validate->getError()]);
            }
            $res = $this->db->_update($data);
            if($res){
                $this->db->commit();
                return $this->Res($res);
            }else{
                $this->db->rollback();
                return $this->error('error');
            }
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id){
        if ($id){
            $info = $this->db->where('id',$id)->find();
        }else{
            $info = '';
        }
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * @param $id
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete($id){
        $info = $this->db->where('id',$id)->field('src')->find();
        $res = $this->db->where('id',$id)->delete();
        if ($info['src']){
            unlink('.'.$info['src']);
        }
        return $this->delRes($res);
    }
    /**
     * @param Request $request
     * @return \think\response\Json
     */
    public function upstatus(){
        $id = $this->request->param('id') ? $this->request->param('id') : '';
        $info = $this->db->where('id',$id)->find();
        if ($info['status'] == 1){
            $res = $this->db->where('id',$id)->update(['status'=>0]);
        }else{
            $res = $this->db->where('id',$id)->update(['status'=>1]);
        }
        return $this->editRes($res);
    }
    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delall(){
        $ids = $this->request->param('id') ? $this->request->param('id') : '';
        $this->db->startTrans();
        $res = $this->db->where('id','in',$ids)->delete();
        if ($res){
            $this->db->commit();
            return $this->delRes($res);
        }else{
            $this->db->rollback();
            return $this->error('error');
        }

    }
}
