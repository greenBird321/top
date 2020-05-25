<?php
/**
 * Created by PhpStorm.
 * User: lihe
 * Date: 2019/3/26
 * Time: 2:16 PM
 */

namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Channels extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("channels");
    }

    public function getChannelsList()
    {
        $sql = "SELECT * from `channels`";
        $query = DI::getDefault()->get('dbBackend')->query($sql);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        return $data;
    }

    public function modifyAuth($type, $user_id, $data)
    {
        if (!$data) {
            $data = [];
        }
        // 删除旧数据
        $sql = "DELETE FROM {$type} WHERE user_id = {$user_id}";
        $re = DI::getDefault()->get('dbBackend')->execute($sql);
        if ($re) {
            foreach ($data as $v) {
                $sql = "INSERT INTO `$type` (`user_id`, `channel_id`) VALUES ('$user_id', '$v')";
                DI::getDefault()->get('dbBackend')->execute($sql);
            }
            return true;
        }
        return false;
    }

    public function getChannelByID($table = 'user_channel', $get_field = 'channel_id', $id = 0)
    {
        $sql = "SELECT {$get_field} FROM {$table} WHERE `user_id` = {$id}";
        $query = DI::getDefault()->get('dbBackend')->query($sql);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        if (!$data) {
            return [];
        }

        return array_map(function () {
            return true;
        }, array_flip(array_column($data, $get_field)));
    }
}