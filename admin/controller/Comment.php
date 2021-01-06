<?php

namespace app\admin\controller;

use app\admin\model\CommentModel;
use think\Controller;

class Comment extends Common
{
    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $db = new CommentModel();
        $data = $this->request->param();
        $list = $db
            ->alias('c')
            ->join('user_post p', ' c.postid=p.id', 'left')
            ->join('app_user u', ' c.uid=u.uid', 'left')
            ->join('user_resources r', ' c.resourcesid=r.id', 'left')
            ->where(function ($query) use ($data) {
                $search = isset($data['status']) ? trim($data['status']) : '';
                if ($search !== '') {
                    $query->where('c.status', $search);
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['body']) ? trim($data['body']) : '';
                if ($search) {
                    $query->where('c.body', 'like', '%' . $search . '%');
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['start']) ? trim($data['start']) : '';
                if ($search) {
                    $query->where('c.create_time', '>=', strtotime($data['start']));
                }
            })
            ->where(function ($query) use ($data) {
                $search = isset($data['end']) ? trim($data['end']) : '';
                if ($search) {
                    $query->where('c.create_time', '<=', strtotime($data['end']));
                }
            })
            ->where(function ($query) use ($data) {
                $search['start'] = isset($data['start']) ? trim($data['start']) : '';
                $search['end'] = isset($data['end']) ? trim($data['end']) : '';
                if ($search['start'] && $search['end']) {
                    $query->where('c.create_time', 'between', [strtotime($search['start']), strtotime($search['end'])]);
                }
            })
            ->field('c.id,u.nickname,c.body,c.postid,r.src,c.likenum,c.status,p.body as pbody,r.type,c.create_time,r.status as rstatus')
            ->order('c.id asc')->paginate(10);
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
        $db = new CommentModel();
        $db->startTrans();
        $res = $db->where('id', $id)->delete();
        if ($res) {
            $db->commit();
            return $this->delRes($res);
        } else {
            $db->rollback();
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
        $db = new CommentModel();
        $id = $this->request->param('id');
        $info = $db->where('id', $id)->find();
        if ($info['status'] == 1) {
            $res = $db->where('id', $id)->update(['status' => 0]);
        } else {
            $res = $db->where('id', $id)->update(['status' => 1]);
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
        $db = new CommentModel();
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
