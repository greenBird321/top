<?php


namespace MyApp\Controllers;


use MyApp\Models\Utils;
use Phalcon\Mvc\Controller;

class PublicController extends Controller
{


    public function initialize()
    {
        $this->view->common = [
            'user_id'   => $this->session->get('user_id'),
            'name'      => $this->session->get('name'),
            'username'  => $this->session->get('username'),
            'avatar'    => $this->session->get('avatar'),
            'menu_true' => $this->session->get('resources')['menu_tree'],
        ];
    }


    public function indexAction()
    {
    }


    // 管理后台的登录处理
    public function loginAction()
    {
        $ticket = $this->request->get('ticket', 'string');

        // BASE URL
        $base_url = $this->config->sso->base_url;

        if (!$ticket) {
            // 检查协议
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
                ? 'https://' : 'http://';
            $callback = $http_type . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $login_url = $base_url . '?redirect=' . urlencode($callback);
            header('Location:' . $login_url);
            exit();
        }

        // 验证ticket
        $verify_url = $base_url . '/verify/' . $ticket;
        $result = file_get_contents($verify_url);
        $result = json_decode($result, true);

        if ($result['code'] != 0) {
            Utils::tips('warning', 'Login Failed');
        }


        // TODO::拿Ticket换取资源 增加APPKEY
        $resource_url = $base_url . '/resources?app=' . $this->config->setting->app_id . '&ticket=' . $ticket;
        $resources = json_decode(file_get_contents($resource_url), true);
        if ($resources['code'] != 0) {
            Utils::tips('warning', 'Error When Get Resources');
        }
        else {
            unset($resources['code'], $resources['msg']);
            $this->session->set('resources', $resources);
        }


        // 设置SESSION
        $this->session->set('user_id', $result['user_id']);
        $this->session->set('username', $result['username']);
        $this->session->set('name', $result['name']);
        $this->session->set('avatar', $result['avatar']);


        header('Location:/');
        exit();
    }


    public function logoutAction()
    {
        $this->persistent->destroy();
        $this->session->destroy();
        $callback = 'http://' . $_SERVER['HTTP_HOST'];
        $logoutUrl = $this->config->sso->base_url . '/logout?redirect=' . urlencode($callback);
        header('Location:' . $logoutUrl);
        exit;
    }


    /**
     * 注销当前已登录的其他系统
     */
    public function logoutOthers()
    {
        $this->persistent->destroy();
        $this->session->destroy();
    }


    public function tipsAction()
    {
        $flashData = json_decode(trim($this->cookies->get('flash')->getValue()), true);
        $this->view->tips = $flashData;
        if (isset($_SERVER['HTTP_X_PJAX'])) {
            $this->view->setMainView('');
        }
        $this->view->pick("public/tipsPjax");
    }


    public function show401Action()
    {
        $this->view->message = 'Error 401, No Permission';
        $this->view->pick("public/errors");
    }


    public function show404Action()
    {
        $this->view->message = 'Error 404, Not Found';
        $this->view->pick("public/errors");
    }


    public function exceptionAction()
    {
        $this->view->message = 'Error 400, Exception Occurs';
        $this->view->pick("public/errors");
    }

}