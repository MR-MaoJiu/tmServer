<?php


namespace app\admin\controller;


use app\admin\model\PostsModel;

class Posts extends Common
{
    protected $db;

    public function initialize()
    {
        parent::initialize();
        $this->db = new PostsModel();
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $data = $this->request->param();
        $list = $this->db
            ->alias('p')
            ->join('user_resources r', ' p.resourcesid=r.id', 'left')
            ->join('app_user u', ' p.uid=u.uid', 'left')
            ->where(function ($query) use ($data) {
                $search = isset($data['status']) ? trim($data['status']) : '';
                if ($search !== '') {
                    $query->where('p.status', $search);
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['type']) ? trim($data['type']) : '';
                if ($search) {
                    $query->where('r.type', $search);
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['start']) ? trim($data['start']) : '';
                if ($search) {
                    $query->where('p.create_time', '>=', strtotime($data['start']));
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['end']) ? trim($data['end']) : '';
                if ($search) {
                    $query->where('p.create_time', '<=', strtotime($data['end']));
                }
            })
            ->where(function ($query) use ($data) {
                $search['start'] = isset($data['start']) ? trim($data['start']) : '';
                $search['end'] = isset($data['end']) ? trim($data['end']) : '';
                if ($search['start'] && $search['end']) {
                    $query->where('p.create_time', 'between', [strtotime($search['start']), strtotime($search['end'])]);
                }
            })
            ->field('p.id,r.type,p.likenum,p.body,r.src,p.longitude,p.latitude,p.status,p.create_time,u.nickname,r.status as rstatus')
            ->order('p.id asc')->paginate();

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
        if ($info['status'] != 0) {
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

    public function maps()
    {
        $data = $this->request->param();
        $this->assign('longitude', $data['longitude']);
        $this->assign('latitude', $data['latitude']);
        return $this->fetch();
    }
}