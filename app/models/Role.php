<?php

namespace MyApp\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Role extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource("roles");
    }


    /**
     * 获取映射关系数据
     * 指定table,get_field,id
     * @param string $table
     * @param string $get_field
     * @param int $id
     * @return array
     */
    public function getResourceByID($table = 'role_user', $get_field = 'role_id', $id = 0)
    {
        $idField = trim(str_ireplace(str_replace('_id', '', $get_field), '', $table), '_') . "_id";
        $sql = "SELECT {$get_field} FROM $table WHERE {$idField}=:id";
        $bind = array('id' => $id);
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        if (!$data) {
            return [];
        }
        return array_map(function () {
            return true;
        }, array_flip(array_column($data, $get_field)));
    }


    /**
     * 根据role_id获取user_id,userName
     * @param null $role_id
     * @return array
     */
    public function getUserByRoleID($role_id = null)
    {
        $sql = "SELECT u.id, u.name, r.role_id FROM `users` u RIGHT JOIN `role_user` r ON u.id=r.user_id WHERE 1=1 ";
        $bind = [];
        if ($role_id) {
            $sql .= "AND r.role_id=:role_id";
            $bind = array('role_id' => $role_id);
        }
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        if (!$data) {
            return [];
        }
        return $data;
    }


    /**
     * 获取所有资源[权限管理]
     * @param string $app
     * @return mixed
     */
    public function getAllResources($app = '')
    {
        $sql = "SELECT id, name, parent, icon
                FROM `resources`
                WHERE status=1 AND app=:app
                ORDER BY sort DESC";
        $bind = array('app' => $app);
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        return $query->fetchAll();
    }


    /**
     * 编辑授权
     * @param string $type
     * @param string $itemID
     * @param array $newData
     * @return bool
     */
    public function modifyAuth($type = 'role_user', $itemID = '', $newData = [])
    {
        if (!$newData) {
            $newData = [];
        }
        switch ($type) {
            case 'role_user':
                $itemKey = 'user_id';
                $dateKey = 'role_id';
                break;
            case 'role_resource':
                $itemKey = 'role_id';
                $dateKey = 'resource_id';
                break;
            case 'user_app':
                $itemKey = 'user_id';
                $dateKey = 'app_id';
                break;
            case 'user_game':
                $itemKey = 'user_id';
                $dateKey = 'game_id';
                break;
            default:
                return false;
        }


        // 旧数据
        $sql = "SELECT `{$dateKey}` FROM `{$type}` WHERE `{$itemKey}`=:itemID";
        $bind = array("itemID" => $itemID);
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $oldData = $query->fetchAll();
        $oldData = array_column($oldData, $dateKey);


        // 自动转换数组结构
        // 如为此格式需转换 $newData = [ '1' => 'false', '2' => 'true', '3' => true];
        $testArray = array_slice($newData, -1);
        $flag = array_pop($testArray);
        if (is_bool($flag) || strtolower($flag) == 'true' || strtolower($flag) == 'false') {
            $newData = array_filter($newData, function ($item) {
                if ($item == 'true') {
                    return true;
                }
                return false;
            });
            $newData = array_keys($newData);
        } else {
            $newData = array_map(function ($item) {
                return trim($item);
            }, $newData);
        }


        // 计算差集
        $delete = array_diff($oldData, $newData);
        $add = array_diff($newData, $oldData);

        // 处理
        if ($delete) {
            $delete = implode(',', $delete);
            $sql = "DELETE FROM `$type` WHERE `$itemKey` = $itemID AND `$dateKey` IN ($delete)";
            DI::getDefault()->get('dbBackend')->execute($sql);
        }
        if ($add) {
            foreach ($add as $v) {
                $sql = "INSERT INTO `$type` (`$itemKey`, `$dateKey`) VALUES ($itemID, '$v')";
                DI::getDefault()->get('dbBackend')->execute($sql);
            }
        }
        return true;
    }


    /**
     * 编辑角色资源【补充modifyAuth方法】
     * @param string $app
     * @param int $role_id
     * @param array $newData
     * @return bool
     */
    public function modifyRoleResource($app = '', $role_id = 0, $newData = [])
    {
        if (!$newData) {
            $newData = [];
        }
        $itemID = $role_id;
        $type = 'role_resource';
        $itemKey = 'role_id';
        $dateKey = 'resource_id';

        // 当前应用资源
        $sql = "SELECT id FROM resources WHERE app=:app";
        $bind = array("app" => $app);
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $resources = array_column($query->fetchAll(), 'id');
        $resources = implode(',', $resources);

        // 旧数据
        $sql = "SELECT `{$dateKey}` FROM `{$type}` WHERE `{$itemKey}`=:itemID AND `$dateKey` IN ({$resources})";
        $bind = array("itemID" => $itemID);
        $query = DI::getDefault()->get('dbBackend')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $oldData = $query->fetchAll();
        $oldData = array_column($oldData, $dateKey);


        // 自动转换数组结构
        // 如为此格式需转换 $newData = [ '1' => 'false', '2' => 'true', '3' => true];
        $testArray = array_slice($newData, -1);
        $flag = array_pop($testArray);
        if (is_bool($flag) || strtolower($flag) == 'true' || strtolower($flag) == 'false') {
            $newData = array_filter($newData, function ($item) {
                if ($item == 'true') {
                    return true;
                }
                return false;
            });
            $newData = array_keys($newData);
        } else {
            $newData = array_map(function ($item) {
                return trim($item);
            }, $newData);
        }


        // 计算差集
        $delete = array_diff($oldData, $newData);
        $add = array_diff($newData, $oldData);

        // 处理
        if ($delete) {
            $delete = implode(',', $delete);
            $sql = "DELETE FROM `$type` WHERE `$itemKey` = $itemID AND `$dateKey` IN ($delete)";
            DI::getDefault()->get('dbBackend')->execute($sql);
        }
        if ($add) {
            foreach ($add as $v) {
                $sql = "INSERT INTO `$type` (`$itemKey`, `$dateKey`) VALUES ($itemID, '$v')";
                DI::getDefault()->get('dbBackend')->execute($sql);
            }
        }
        return true;
    }

}