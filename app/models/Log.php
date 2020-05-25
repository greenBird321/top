<?php

namespace MyApp\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Log extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
    }


    // 操作日志
    public function operationLog()
    {
        $uri = (strpos($_SERVER['REQUEST_URI'], '?') === false) ?
            $_SERVER['REQUEST_URI'] :
            substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
        $id = DI::getDefault()->get('request')->get('id', 'int', 0);
        try {
            $data = $_POST;
            if (array_key_exists('pwd', $data)) {
                $data['pwd'] = '***';
            }
            if (array_key_exists('password', $data)) {
                $data['password'] = '***';
            }
            if (array_key_exists('re_password', $data)) {
                $data['re_password'] = '***';
            }
            DI::getDefault()->get('dbBackend')->insertAsDict(
                "logs_operation",
                array(
                    "user_id"     => DI::getDefault()->get('session')->get('user_id'),
                    "resource"    => $uri,
                    "data_id"     => $id,
                    "data"        => json_encode($data, JSON_UNESCAPED_UNICODE),
                    "ip"          => DI::getDefault()->get('request')->getClientAddress(),
                    "create_time" => date('Y-m-d H:i:s'),
                )
            );
        } catch (\Exception $e) {
        }
    }

}