<?php

namespace MyApp\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class SessionLog extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("logs_session");
    }


    /**
     * 用户当前已经登录的系统的URL
     * @param $redirect
     */
    public static function store($user_id, $website, $session_id)
    {
        return DI::getDefault()->get('dbBackend')->insertAsDict(
            "logs_session",
            array(
                "user_id" => $user_id,
                "session_id" => $session_id,
                "website" => $website,
                "create_time" => date('Y-m-d H:i:s'),
            )
        );
    }

}