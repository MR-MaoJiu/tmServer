<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;

class Index extends Common
{
    /**
     * @return mixed
     */
    public function index()
    {
       return $this->fetch();
    }
    /**
     * @return mixed
     */
    public function welcome(){
        return $this->fetch();
    }

}
