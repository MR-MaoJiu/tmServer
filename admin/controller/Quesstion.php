<?php
namespace app\admin\controller;

use app\admin\model\QuesstionModel;
use think\Controller;

class Quesstion extends Common
{
    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $db = new QuesstionModel();
        $data = $this->request->param();
        $list = $db
            ->where(function ($query) use ($data) {
                $search = isset($data['text']) ? trim($data['text']) : '';
                if ($search) {
                    $query->where('text', 'like', '%' . $search . '%');
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['quesstion']) ? trim($data['quesstion']) : '';
                if ($search) {
                    $query->where('quesstion', 'like', '%' . $search . '%');
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
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $db = new QuesstionModel();
        $db->startTrans();
        $res = $db->where('id',$id)->delete();
        if ($res){
            $db->commit();
            return $this->delRes($res);
        }else{
            $db->rollback();
            return $this->delRes($res);
        }
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delall()
    {
        $db = new QuesstionModel();
        $ids = $this->request->param('ids');
        $db->startTrans();
        $res = $db->where('id', 'in', $ids)->delete();
        if ($res) {
            $db->commit();
            return $this->delRes($res);
        } else {
            $db->rollback();
            return $this->delRes($res);
        }
    }
}
