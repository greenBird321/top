<?php
/**
 * Created by PhpStorm.
 * User: lihe
 * Date: 2017/3/31
 * Time: 下午12:17
 */
namespace MyApp\Controllers;

use MyApp\Models\User;
use MyApp\Models\Page;
use MyApp\Models\Utils;
use Phalcon\Mvc\Dispatcher;
use MyApp\Models\System;

class SystemController extends ControllerBase
{

    private $_systemModel;
    private $_usersModel;
    private $_pageModel;

    public function initialize()
    {
        parent::initialize();
        $this->_systemModel = new System();
        $this->_usersModel = new User();
        $this->_pageModel = new Page();
    }

    public function indexAction()
    {
    }

    public function logsAction()
    {

        if ($this->request->isPost()) {
            $limit = $this->request->get('pageSize', 'int', 15);
            $offset = $this->request->get('startIndex', 'int', 0);
            $start_time = $this->request->get('start_time', 'string');
            $end_time = $this->request->get('end_time', 'string');
            $data = [
                'start_time' => $start_time,
                'end_time' => $end_time
            ];
            $total = $this->_systemModel->getLogsCount($data);
            $logs = $this->_systemModel->getList($data, $offset, $limit);
            foreach ($logs as $k => $v) {
                $logs[$k]['user'] = $this->_usersModel->getUserNameById($v['user_id']);
            }
            $result = [
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $logs
            ];
            Utils::outputJSON($result);
        }

    }

}