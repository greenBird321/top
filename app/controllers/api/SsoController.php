<?php

namespace MyApp\Controllers\Api;


use MyApp\Models\Auth;
use MyApp\Models\SessionLog;
use MyApp\Models\Utils;
use Endroid\QrCode\QrCode;
use PHPGangsta_GoogleAuthenticator;

class SsoController extends ControllerBase
{

    private $authModel;
    private $sessionLogModel;
    private $utilsModel;


    public function initialize()
    {
        $this->authModel = new Auth();
        $this->utilsModel = new Utils();
        $this->sessionLogModel = new SessionLog();
    }


    public function indexAction()
    {
        $this->loginAction();
    }


    public function loginAction()
    {
        //$password = password_hash($password, PASSWORD_DEFAULT);
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $ipAddress = $this->request->getClientAddress();
        $location = $this->utilsModel->getLocation($ipAddress);
        $redirect = $this->request->get('redirect', 'string');
        if ($_POST) {
            $username = trim($this->request->getPost('username', 'email'));
            $password = trim($this->request->getPost('password', 'string'));
            $captcha = $this->request->getPost('captcha', 'alphanum');
            $user_agent = $this->request->getUserAgent();


            if (!($username && $password)) {
                Utils::tips('warning', 'Empty User Nor Password');
            }


            // 安全检查 TODO::验证码
            if (!$this->authModel->checkLoginTimes($ipAddress)) {
                Utils::tips('warning', 'Login Failed Too Much Time');
            }


            // 查询用户
            $userData = $this->authModel->getUser($username);
            if (!$userData) {
                Utils::tips('warning', 'User Is Not Exist');
            }


            // 验证密码
            $verify_result = password_verify($password, $userData['password']);

            // 登录日志
            $log = array(
                'user_id'    => $userData['id'],
                'ip'         => $ipAddress,
                'location'   => $location,
                'user_agent' => $user_agent,
                'referer'    => $referer,
                'result'     => $verify_result ? 1 : 0,
            );
            $this->authModel->loginLog($log);

            // 检查
            if (!$verify_result) {
                Utils::tips('warning', 'Password Error');
            }
            if ($userData['status'] != 1) {
                Utils::tips('warning', 'The User Is Limited');
            }

            // 查询role_id并合并
            $role_id = $this->authModel->getRoleID($userData['id']);

            // 设置SESSION
            $this->session->set('user_id', $userData['id']);
            $this->session->set('username', $userData['username']);
            $this->session->set('name', $userData['name']);
        }


        // 已登录
        $user_id = $this->session->get('user_id');
        if ($user_id) {
            // 生成Ticket
            $ticket = $this->authModel->createTicket($user_id);

            // 回调地址
            if (strpos($redirect, '?')) {
                $redirect .= '&ticket=' . $ticket;
            }
            else {
                $redirect .= '?ticket=' . $ticket;
            }
            if (strpos($redirect, 'http') !== 0) {
                $redirect = 'http://' . $redirect;
            }

            // 检查令牌
            $userData = $this->authModel->getUser($user_id);
            if (empty($userData['secret_key'])) {
                $this->session->set('is_login', 1);
                $this->authModel->securityCheck($userData, $location);
                header("Location:" . $redirect);
            }
            else {
                $this->session->set('redirect', $redirect);
                header("Location: /api/sso/OTPAuth");
            }
            exit();
        }


        $this->view->redirect = $redirect;
        $this->view->pick('sso/index');
    }


    public function OTPAuthAction()
    {
        $redirect = $this->session->get('redirect');
        if (!$redirect) {
            header('Location: /api/sso/login');
            exit();
        }
        if ($_POST) {
            $code = $this->request->get('code', 'int');

            $user_id = $this->session->get('user_id');
            $user = $this->authModel->getUser($user_id);

            $otp = new PHPGangsta_GoogleAuthenticator();
            $checkResult = $otp->verifyCode($user['secret_key'], $code, 2);    // 2 = 2*30sec clock tolerance
            $this->session->set('redirect', null);
            if (!$checkResult) {
                Utils::tips('warning', 'Authenticator Code Is Error');
            }
            $this->session->set('is_login', 1);
            header("Location:" . $redirect);
            exit();
        }
        $this->view->pick('sso/OTPAuth');
    }


    public function logoutAction()
    {
        $redirect = $this->request->get('redirect', 'string');
        $this->logoutOthers();
        $this->persistent->destroy();
        $this->session->destroy();
        if ($redirect) {
            header('Location:' . $redirect);
            exit;
        }
    }


    /**
     * 注销其它已登录的系统
     */
    public function logoutOthers()
    {
        $user_id = $this->session->get('user_id');
        $sessionLogObj = SessionLog::find([
            "user_id = ?1",
            "bind" => [1 => $user_id]
        ]);
        $sessions = $sessionLogObj->toArray();
        foreach ($sessions as $session) {
            $logoutUrl = $session['website'] . '/logoutOthers';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $logoutUrl);
            curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $session['session_id']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        }
        //删除登录记录
        $sessionLogObj->delete();
    }


    public function qrAction()
    {
        $username = $this->session->get('username');
        if (!$username) {
            $this->response->setJsonContent(['code' => 1, 'msg' => 'No Permission'])->send();
            exit();
        }

        // 二维码生成与验证
        $otp = new PHPGangsta_GoogleAuthenticator();
        if ($_POST) { // 验证 开启二次验证是否正确
            $code = $this->request->get('code', 'int');
            $secret_key = $this->session->get('secret_key');
            $checkResult = $otp->verifyCode($secret_key, $code, 2);
            // TODO :: 梳理
            if (!$checkResult) {
                $this->response->setJsonContent(['code' => 0, 'msg' => 'Verify Success'])->send();
                $user_id = $this->session->get('user_id');
                $this->authModel->setOTPKey($user_id, $secret_key);
            }
            $this->response->setJsonContent(['code' => 1, 'msg' => 'Verify Failed'])->send();
            exit();
        }
        $secret_key = $otp->createSecret(32);
        $this->session->set('secret_key', $secret_key);
        $username = urlencode('账号：') . $username;
        $url = "otpauth://totp/{$username}?secret={$secret_key}&issuer=" . urlencode('XXTIME.COM');
        $qrCode = new QrCode();
        $qrCode
            ->setText($url)
            ->setSize(200)
            ->setPadding(10)
            ->setErrorCorrection('low')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            //->setLabel('xxtime.com')
            //->setLabelFontSize(8)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        header('Content-Type: ' . $qrCode->getContentType());
        $qrCode->render();
        exit;
    }


    public function verifyAction()
    {
        $argv = $this->dispatcher->getParams();
        if (!isset($argv['0'])) {
            $this->response->setJsonContent([
                'code' => 1,
                'msg'  => 'invalid argv'
            ])->send();
            exit();
        }
        $ticket = $argv['0'];
        $user = $this->authModel->getUserByTicket($ticket);
        $role_id = $this->authModel->getRoleID($user['id']);

        if (!$user) {
            $this->response->setJsonContent([
                'code' => 1,
                'msg'  => 'failed'
            ])->send();
            exit();
        }


        if (empty($user['avatar'])) {
            $user['avatar'] = 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim($user['username']))) . '?s=80&d=identicon';
        }
        $this->response->setJsonContent(
            array_merge(
                [
                    'code' => 0,
                    'msg'  => 'success'
                ]
                ,
                [
                    'user_id'  => $user['id'],
                    'username' => $user['username'],
                    'name'     => $user['name'],
                    'avatar'   => $user['avatar'],
                    'role_id'  => $role_id
                ]
            ))->send();
        exit();
    }


    public function resourcesAction()
    {
        $app = $this->request->get('app', 'string');
        $ticket = $this->request->get('ticket', 'string');
        $user = $this->authModel->getUserByTicket($ticket);
        if (!$user) {
            $this->response->setJsonContent([
                'code' => 1,
                'msg'  => 'failed'
            ])->send();
            exit;
        }


        // ACL是phalcon的ACL指定格式
        $allow_resources = $this->authModel->getResources($user['id'], $app);
        $result['acl_all'] = $this->authModel->getAclFormat($this->authModel->getResources(1000, $app));
        $result['acl_allow'] = $this->authModel->getAclFormat($allow_resources);
        $result['menu_tree'] = $this->utilsModel->list2tree($allow_resources);
        $result['allow_game'] = $this->authModel->getGames($user['id']);
        $result['allow_channel'] = $this->authModel->getChannel($user['id']);

        $this->response->setJsonContent([
                'code' => 0,
                'msg'  => 'success'
            ] + $result)->send();
        exit;
    }

}