<?php
/**
 * Created by PhpStorm.
 * User: lihe
 * Date: 2017/3/31
 * Time: 下午12:17
 */
namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class System extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("logs_operation");
    }

    public function getLogsCount($data)
    {
        $result = $this->getWhere($data);
        $query = DI::getDefault()->get('dbBackend')->query($result['where'], $result['bind']);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $log = $query->fetchAll();
        return count($log);
    }

    public function getList($data, $offset, $limit)
    {
        $result = $this->getWhere($data);
        $result['where'] .= " limit $offset,$limit";
        $query = DI::getDefault()->get('dbBackend')->query($result['where'], $result['bind']);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $logs = $query->fetchAll();
        return $logs;
    }

    public function getWhere($data)
    {
        $where = "SELECT id,user_id,data_id,resource,ip,create_time FROM `logs_operation` WHERE 1=1";
        $bind = [];
        if (!empty($data['start_time'])) {
            $where .= " AND create_time >= :start_time";
            $bind['start_time'] = $data['start_time'] . ' 00:00:00';
        }
        if (!empty($data['end_time'])) {
            $where .= " AND create_time <= :end_time";
            $bind['end_time'] = $data['end_time'] . ' 23:59:59';
        }
        $where .= " ORDER BY `id` DESC";
        return ['where' => $where, 'bind' => $bind];
    }
}