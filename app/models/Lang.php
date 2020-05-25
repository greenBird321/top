<?php
/**
 * Created by PhpStorm.
 * User: lihe
 * Date: 2017/5/12
 * Time: 上午11:09
 */
namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Lang extends Model
{

    public function initialize()
    {
        $this->setConnectionService('dbBackend');
        $this->setSource('lang');
    }

    public function getGameLang()
    {
        $sql = "SELECT * FROM lang ORDER BY id";
        $query = DI::getDefault()->get('dbBackend')->query($sql);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        return $data;
    }
}
