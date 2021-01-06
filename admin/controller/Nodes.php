<?php

namespace app\admin\controller;

use app\admin\model\NodesModel;
use app\admin\validate\NodesValidate;
use think\Controller;
use think\Db;
use think\Request;

class Nodes extends Common
{
    protected $db;
    protected $validate;

    public function __construct()
    {
        parent::__construct();
        $this->validate = new NodesValidate();
        $this->db = new NodesModel();
    }

    /**
     * 显示资源列表
     *
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
                $search = isset($data['url']) ? trim($data['url']) : '';
                if ($search) {
                    $query->where('url', 'like', '%' . $search . '%');
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
            ->order('id asc')->paginate(9);

        $this->assign('list', $list);
        $this->assign('total', $list->count());
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
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
        $data = $this->request->param();
        $this->db->startTrans();
        if (!$this->validate->check($data)) {
            return json(['code' => 400, 'msg' => $this->validate->getError()]);
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
        $info = $this->db->where('id',$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete()
    {
        $this->db->startTrans();
        $id = $this->request->param('id') ? $this->request->param('id') : '';
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

    /**
     * @return mixed|void
     */
    public function error1(){
        return $this->fetch();
    }
}
