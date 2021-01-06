<?php


namespace app\admin\controller;


use app\admin\model\ResourceModel;
use think\Db;
use think\Exception;
use think\Request;

class Resource extends Common
{
    public function __construct()
    {
        parent::__construct();
        $this->db = new ResourceModel();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = $this->request->param();
        $list = $this->db
            ->alias('r')
            ->join('app_user u', ' r.uid=u.uid', 'left')
            ->where(function ($query) use ($data) {
                $search = isset($data['status']) ? trim($data['status']) : '';
                if ($search !== '') {
                    $query->where('status', $search);
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['type']) ? trim($data['type']) : '';
                if ($search) {
                    $query->where('type', $search);
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['start']) ? trim($data['start']) : '';
                if ($search) {
                    $query->where('r.create_time', '>=', strtotime($data['start']));
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['end']) ? trim($data['end']) : '';
                if ($search) {
                    $query->where('r.create_time', '<=', strtotime($data['end']));
                }
            })
            ->where(function ($query) use ($data) {
                $search['start'] = isset($data['start']) ? trim($data['start']) : '';
                $search['end'] = isset($data['end']) ? trim($data['end']) : '';
                if ($search['start'] && $search['end']) {
                    $query->where('r.create_time', 'between', [strtotime($search['start']), strtotime($search['end'])]);
                }
            })
            ->field('r.id,u.nickname,r.src,r.type,r.status,r.usefulness,r.create_time')
            ->order('r.id asc')->paginate();
        //->order('id asc')->paginate(10);

        $this->assign('list', $list);
        $this->assign('total', $list->count());

        return $this->fetch();
    }

    /**
     * 禁用启用
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function upstatus()
    {
        $id = $this->request->param('id');
        $info = $this->db->where('id', $id)->find();
        if ($info['status'] != 0) {
            $res = $this->db->where('id', $id)->update(['status' => 0]);
        } else {
            $res = $this->db->where('id', $id)->update(['status' => 1]);
        }
        return $this->editres($res);
    }
//    /**
//     * 显示创建资源表单页.
//     *
//     * @return \think\Response
//     */
//    public function create()
//    {
//        $dsj_data = Db::name('dsj')->where('status', 1)->field('id,name')->select();
//        $this->assign('dsj_data', $dsj_data);
//        return $this->fetch();
//    }

//    /**
//     * 保存新建的资源
//     *
//     * @param \think\Request $request
//     * @return \think\Response
//     */
//    public function save()
//    {
//        try {
//            $this->db->startTrans();
//            $data = $this->request->param();
//            $validate = new JujiValidate();
//            $data['create_time'] = time();
//            if (!$validate->check($data)) {
//                return json(['code' => 0, 'message' => $validate->getError()]);
//            }
//            $res = $this->db->_update($data);
//            if ($res) {
//                $this->db->commit();
//                return $this->Res($res);
//            } else {
//                return $this->error('error');
//            }
//
//        } catch (Exception $e) {
//            return json(['code' => 400, 'message' => $e->getMessage()]);
//        }
//
//    }

//    /**
//     * 显示编辑资源表单页.
//     *
//     * @param int $id
//     * @return \think\Response
//     */
//    public function edit($id)
//    {
//        $dsj_data = Db::table('jytx_dsj')->where('status', 1)->field('id,name')->select();
//        $this->assign('dsj_data', $dsj_data);
//        $info = $this->db->where('id', $id)->find();
//        $this->assign('info', $info);
//        return $this->fetch();
//    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $this->db->startTrans();
        $res = $this->db->where('id', $id)->delete();
        $info = $this->db->where('id', $id)->field('src')->find();
        if ($res) {
            if (isset($info['src'])) {
                unlink("." . $info['src']);
            }
            $this->db->commit();
            return json(['code' => 1, 'message' => 'success']);
        }
        $this->db->rollback();
        return json(['code' => 0, 'message' => 'error']);
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delall()
    {
        $ids = $this->request->param('ids') ? $this->request->param('ids') : '';
        $this->db->startTrans();
        $res = $this->db->where('id', 'in', $ids)->delete();
        if ($res) {
            $this->db->commit();
            return $this->delRes($res);
        }
        $this->db->rollback();
        return $this->error('error');
    }
}
