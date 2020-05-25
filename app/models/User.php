<?php

namespace MyApp\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class User extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("users");
    }


    public function getList()
    {
        $sql = "SELECT id,username,name,status,mobile,avatar,create_time,update_time FROM users WHERE 1=1";
        $query = DI::getDefault()->get('dbBackend')->query($sql);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        if (!$data) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (empty($value['avatar'])) {
                $data[$key]['avatar'] = $user['avatar'] = $this->getUserAvatar($value['username']);
            }
        }
        return $data;
    }


    public function getUserAvatar($username = '')
    {
        return 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim($username))) . '?s=80&d=identicon';
    }

    public function getUserNameById($id = '')
    {
        $sql = "SELECT `name` FROM `users` WHERE id=:id";
        $bind = [
            'id' => $id
        ];
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetch();
        return $data['name'] ? $data['name'] : '未知';
    }

    public function getUserInfo($id)
    {
        $sql = "SELECT * FROM `users` WHERE  id=:id";
        $bind = [
            'id' => $id
        ];
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetch();
        return $data;
    }
}