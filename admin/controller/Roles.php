<?php

namespace app\admin\controller;

use app\admin\model\NodesModel;
use app\admin\model\RolesModel;
use app\admin\validate\RolesValidate;
use think\Controller;
use think\Db;
use think\Request;

class Roles extends Common
{
    protected $db;
    protected $validate;

    public function __construct()
    {
        parent::__construct();
        $this->validate = new RolesValidate();
        $this->db = new RolesModel();
    }

    /**
     * 角色列表
     * 显示资源列表
     * @return \think\Response
     */
    public function index()
    {
        $data = $this->request->param();
        $data['status'] = '';
        $list = $this->db
            ->where(function ($query) use ($data) {
                $search = isset($data['status']) ? trim($data['status']) : '';
                if ($search !== '') {
                    $query->where('status', $search);
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['name']) ? trim($data['name']) : '';
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['start']) ? trim($data['start']) : '';
                if ($search) {
                    $query->where('create_time', '>=', strtotime($data['start']));
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['end']) ? trim($data['end']) : '';
                if ($search) {
                    $query->where('create_time', '<=', strtotime($data['end']));
                }
            })
            ->where(function ($query) use ($data) {
                $search['start'] = isset($data['start']) ? trim($data['start']) : '';
                $search['end'] = isset($data['end']) ? trim($data['end']) : '';
                if ($search['start'] && $search['end']) {
                    $query->where('create_time', 'between', [strtotime($search['start']), strtotime($search['end'])]);
                }
            })
            ->order('id asc')->paginate(10);

        $this->assign('list', $list);
        $this->assign('total', $list->count());
        return $this->fetch();
    }

    /**
     * 角色添加
     * @return \think\Response
     */
    public function create()
    {
        $nodes = NodesModel::where('status',1)->all();
        $this->assign('nodes', $nodes);
        return $this->fetch();
    }

    /**
     * 角色添加和修改
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->post();
        $this->db->startTrans();
        if (!$this->validate->check($data)) {
            return json(['code' => 400, 'msg' => $this->validate->getError()]);
        }
        $where = [];
        if (empty($data['nid'])){
            $data['nid'] = '';
            $data['belong_nodes'] = '';
        }else{
            if (is_array($data['nid'])){
                $where[] = ['id','in',$data['nid']];
                $role_nodes = Db::name('nodes')->where($where)->where('status',1)->select();
                $data['belong_nodes'] = implode(",",array_column($role_nodes,'name'));
                $data['nid'] = implode(",",$data['nid'])?implode(",",$data['nid']):'';
            }else{
                $where['id'] = ['eq',$data['nid']];
                $role_nodes = NodesModel::where($where)->find();
                $data['belong_nodes'] = $role_nodes['name'];
                $data['nid'] = $data['nid'] ? $data['nid'] :'';
            }
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

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $nodes = NodesModel::where('status', 1)->all();
        $this->assign('nodes', $nodes);
        $info = $this->db->where('id', $id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }

  
    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $this->db->startTrans();
        $res = $this->db->where('id',$id)->delete();
         if($res){
            $this->db->commit();
            return $this->delRes($res);
        }else{
            $this->db->rollback();
            return $this->error('error');
        }
    }

    /**
     * 禁用启用
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function upstatus(){
        $id = $this->request->param('id');
        $info = $this->db->where('id',$id)->find();
        if ($info['status'] == 1){
            $res = $this->db->where('id',$id)->update(['status'=>0]);
        }else{
            $res = $this->db->where('id',$id)->update(['status'=>1]);
        }
        return $this->editres($res);
    }

    /**
     * 批量删除
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delall()
    {
        $this->db->startTrans();
        $ids = $this->request->param('ids');
        $res = $this->db->where('id', 'in', $ids)->delete();
        if($res){
            $this->db->commit();
            return $this->delRes($res);
        }else{
            $this->db->rollback();
            return $this->error('error');
        }
        
    }
}
