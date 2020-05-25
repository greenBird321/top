<?php

namespace MyApp\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Resource extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("resources");
    }

    public function getResource($app = '')
    {
        $sql = "SELECT * FROM resources WHERE app=:app ORDER BY `sort` DESC ";
        $bind = array('app' => $app);
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        return $data;
    }

    public function getResourceDetail($id = '')
    {
        $sql = "SELECT * FROM resources WHERE id=:id";
        $bind = array('id' => $id);
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetch();
        return $data;
    }

    public function modifyResource($param = array())
    {
        $time = date('Y-m-d H:i:s');
        $sql = "UPDATE resources SET app=:app, name=:name, resource=:resource, type=:type, sort=:sort,icon=:icon, status=:status, update_time=:time WHERE id=:id";
        $bind = array(
            'id' => $param['id'],
            'app' => $param['app'],
            'name' => $param['name'],
            'resource' => $param['resource'],
            'type' => $param['type'],
            'sort' => $param['sort'],
            'icon' => $param['icon'],
            'status' => $param['status'],
            'time' => $time
        );
        $exec = DI::getDefault()->get('dbBackend')->execute($sql, $bind);
        return $exec;
    }

    public function createResource($param = array())
    {
        $sql = "INSERT INTO `resources` (`app`, `name`, `resource`, `type`, `parent`, `icon`, `status`, `remark`, `create_time`) VALUES 
                (:app, :name, :resource, :type, :parent, :icon,:status, :remark, :create_time)";
        $bind = array(
            'app' => $param['app'],
            'name' => $param['name'],
            'resource' => $param['resource'],
            'type' => $param['type'],
            'parent' => $param['parent'],
            'icon' => $param['icon'],
            'status' => $param['status'],
            'remark' => $param['remark'],
            'create_time' => $param['create_time']
        );
        $exec = DI::getDefault()->get('dbBackend')->execute($sql, $bind);
        return $exec;
    }

    public function removeResource($id){
        $sql = "DELETE FROM `resources` WHERE id=:id";
        $bind = array(
            "id" => $id
        );
        $exec = DI::getDefault()->get('dbBackend')->execute($sql, $bind);
        return $exec;
    }

    public function updateSort($id,$sort)
    {
        $sql = "UPDATE `resources` set `sort`=:sort WHERE id=:id";
        $bind = [
            'sort' => $sort,
            'id' => $id
        ];
        $exec = DI::getDefault()->get('dbBackend')->execute($sql, $bind);
        return $exec;
    }

    public function updateParent($id, $parent = 0)
    {
        $sql = "UPDATE `resources` set `parent`=:parent WHERE `id`=:id";
        $bind = [
            'parent' => $parent,
            'id'     => $id
        ];
        $exec = DI::getDefault()->get('dbBackend')->execute($sql, $bind);
        return $exec;
    }
}