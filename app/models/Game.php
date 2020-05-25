<?php

namespace MyApp\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Game extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("games");
    }


    public function getList()
    {
        $sql = "SELECT id,game_id,class_id,version,name,en_name,status,domain,create_time FROM games WHERE 1=1";
        $query = DI::getDefault()->get('dbBackend')->query($sql);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        return $data;
    }

    public function updateGameID($id, $game_id)
    {
        $sql = "UPDATE games SET game_id = :game_id WHERE id = :id";
        $bind = [
            'game_id' => $game_id,
            'id'      => $id
        ];
        $exec = DI::getDefault()->get('dbBackend')->execute($sql, $bind);
        return $exec;
    }

    public function getGame($gameid)
    {
        $sql = "SELECT * FROM games WHERE 1=1 AND game_id LIKE '" . $gameid . "%' ORDER BY id DESC";
        $query = DI::getDefault()->get('dbBackend')->query($sql);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        return $data;
    }

}