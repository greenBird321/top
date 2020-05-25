<?php
/**
 * Created by PhpStorm.
 * User: lihe
 * Date: 2017/5/12
 * Time: 上午10:56
 */
namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Classes extends Model{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("class");
    }

    public function getAllClass(){
        $sql = "SELECT id, class_id, name, icon FROM class ORDER BY id";
        $query = DI::getDefault()->get('dbBackend')->query($sql);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        return $data;
    }

    public function getClassName($id){
        $sql = "SELECT class_id FROM class WHERE id=:id";
        $bind = [
            'id' => $id
        ];
        $query = DI::getDefault()->get('dbBackend')->query($sql , $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetch();
        return $data['class_id'];
    }
}