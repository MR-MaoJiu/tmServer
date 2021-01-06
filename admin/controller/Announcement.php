<?php


namespace app\admin\controller;


use app\admin\model\AnnouncementModel;
use app\admin\validate\AnnouncementValidate;

class Announcement extends Common
{
    protected $db;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new AnnouncementValidate();
        $this->db = new AnnouncementModel();
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
        $this->db->startTrans();
        $data = $this->request->post();
        if (!$this->validate->check($data)) {
            return json(['code' => 400, 'msg' => $this->validate->getError()]);
        }

        $data['create_time'] = time();
        $res = $this->db->_update($data);
        if ($res) {
            $this->db->commit();
            return $this->Res($res);
        } else {
            $this->db->rollback();
            return $this->Res($res);
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

        $info = $this->db->where('id', $id)->find();
        $this->assign('info', $info);
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
        $res = $this->db->where('id', $id)->delete();
        if ($res) {
            $this->db->commit();
            return $this->delRes($res);
        } else {
            $this->db->rollback();
            return $this->delRes($res);
        }
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
        if ($info['status'] == 1) {
            $res = $this->db->where('id', $id)->update(['status' => 0]);
        } else {
            $res = $this->db->where('id', $id)->update(['status' => 1]);
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
        $ids = $this->request->param('ids');
        $this->db->startTrans();
        $res = $this->db->where('id', 'in', $ids)->delete();
        if ($res) {
            $this->db->commit();
            return $this->delRes($res);
        } else {
            $this->db->rollback();
            return $this->delRes($res);
        }


    }
}