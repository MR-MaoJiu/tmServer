<?php

namespace app\admin\controller;

use app\admin\model\HomeUserModel;
use think\Controller;
use function Sodium\add;


class Homeuser extends Common
{
    protected $db;

    public function initialize()
    {
        parent::initialize();
        $this->db = new HomeUserModel();
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $data = $this->request->param();
        $list = $this->db
            ->where(function ($query) use ($data) {
                $search = isset($data['onlinestatus']) ? trim($data['onlinestatus']) : '';

                if ($search !== '') {

                    $query->where('onlinestatus', intval($search));
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['status']) ? trim($data['status']) : '';

                if ($search !== '') {

                    $query->where('u.status', intval($search));
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['phone']) ? trim($data['phone']) : '';
                if ($search) {
                    $query->where('phone', 'like', '%' . $search . '%');
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
            ->alias('u')
            ->join('user_resources i', ' u.uid=i.uid', 'left')
            ->field('u.uid,u.nickname,u.phone,u.age,u.gander,i.src,u.address,u.onlinestatus,u.status as ustatus,i.status,i.usefulness,u.create_time,u.last_login_time,u.login_count,u.ip,u.longitude,u.latitude')
            ->order('u.uid asc')->paginate();

        $list = $list ? $list : '';
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
        $this->db->startTrans();
        $res = $this->db->where('uid', $id)->delete();
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
        $db = new HomeUserModel();
        $id = $this->request->param('id');
        $info = $db->where('uid', $id)->find();
        if ($info['status'] == 1) {
            $res = $db->where('uid', $id)->update(['status' => 0]);
        } else {
            $res = $db->where('uid', $id)->update(['status' => 1]);
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
        $res = $this->db->where('uid', 'in', $ids)->delete();
        if ($res) {
            $this->db->commit();
            return $this->delRes($res);
        } else {
            $this->db->rollback();
            return $this->delRes($res);
        }
    }

    public function maps()
    {
        $data = $this->request->param();
        $this->assign('longitude', $data['longitude']);
        $this->assign('latitude', $data['latitude']);
        return $this->fetch();
    }
}
