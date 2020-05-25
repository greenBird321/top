<?php


namespace MyApp\Controllers;


use MyApp\Models\User;
use MyApp\Models\Utils;
use Phalcon\Mvc\Dispatcher;

class AccountController extends ControllerBase
{

    private $userModel;


    public function initialize()
    {
        parent::initialize();
        $this->userModel = new User();
    }


    public function indexAction()
    {
    }


    public function passwordAction()
    {
        $user_id = $this->session->get('user_id');
        $avater = $this->session->get('avatar');
        //通过user_id 查询具体的用户信息
        $user = $this->userModel->getUserInfo($user_id);
        $this->view->pick("account/password");
        $this->view->user = $user;
        $this->view->avater = $avater;

        if ($_POST) {
            $old_pwd = $this->request->get('pwd', ['string', 'trim']);
            $password = $this->request->get('password', ['string', 'trim']);
            $re_password = $this->request->get('re_password', ['string', 'trim']);
            if (!$old_pwd || !$password) {
                Utils::tips('error', '内容不完整', '/account/password');
            }
            if ($re_password != $password) {
                Utils::tips('error', '两次密码不相同', '/account/password');
            }
            if (strlen($password) < 6 || !preg_match("/^[\w]*((\d_?[a-z])|([a-z]_?\d))[\w]*$/i", $password)) {
                Utils::tips('error', '密码太简单', '/account/password');
            }

            $user = $this->userModel->findFirst($this->_user_id);
            if (!password_verify($old_pwd, $user->password)) {
                Utils::tips('error', '原始密码错误', '/account/password');
            }

            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->update_time = date('Y-m-d H:i:s');
            if (!$user->save()) {
                Utils::tips('error', '修改失败', '/account/password');
            }
            Utils::tips('success', '修改成功', '/account/password');
        }
    }

}