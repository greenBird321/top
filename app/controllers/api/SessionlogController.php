<?php


namespace MyApp\Controllers\Api;

use MyApp\Models\SessionLog;


class SessionlogController extends ControllerBase
{


    public function initialize()
    {

    }

    public function indexAction()
    {
        $this->storeAction();
    }

    public function storeAction()
    {
        $data = [
            'user_id' => $this->request->get('user_id', 'string'),
            'website' => $this->request->get('website', 'string'),
            'session_id' => $this->request->get('session_id', 'string')
        ];
        ksort($data);
        $sign = $this->request->get('sign', 'string');
        $sign_true = md5(http_build_query($data) . $this->config->setting->secret_key);

        if ($sign != $sign_true) {
            $this->response->setJsonContent(['code' => 1, 'msg' => 'check sign failed'])->send();
            exit();
        }

        $result = SessionLog::store($data['user_id'], $data['website'], $data['session_id']);

        $response = $result ? ['code' => 0, 'msg' => 'save session success'] : ['code' => 1, 'msg' => 'save session failed'];

        $this->response->setJsonContent($response)->send();
        exit();
    }
}