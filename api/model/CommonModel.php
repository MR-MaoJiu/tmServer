<?php

namespace app\api\model;

use think\Model;

class CommonModel extends Model
{
    protected $pk;
    protected $table;
    protected $autoWriteTimestamp = true;
    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->table = $this->getTable();
        $this->pk = $this->getPk();
    }
    /**
     * @param $data
     * @return bool
     * 添加或修改数据
     */
    public function _update($data){
        if (isset($data['id'])){
            return $this->save($data,['id'=>$data['id']]);
        }else{
            return $this->save($data);
        }
    }


}