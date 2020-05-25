<?php

namespace MyApp\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class App extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("apps");
    }


    public function getList()
    {
        $sql = "SELECT id,app_id,version,name,en_name,status,domain,create_time FROM apps WHERE 1=1";
        $query = DI::getDefault()->get('dbBackend')->query($sql);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        return $data;
    }

}