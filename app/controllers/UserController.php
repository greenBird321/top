<?php


namespace MyApp\Controllers;


use MyApp\Models\Channels;
use MyApp\Models\Game;
use MyApp\Models\Role;
use MyApp\Models\User;
use MyApp\Models\Utils;
use Phalcon\Mvc\Dispatcher;

class UserController extends ControllerBase
{

    private $userModel;
    private $roleModel;
    private $gameModel;
    private $channelsModel;

    public function initialize()
    {
        parent::initialize();
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->gameModel = new Game();
        $this->channelsModel = new Channels();
    }


    public function indexAction()
    {
        $this->view->users = $this->userModel->getList();
    }


    public function newAction()
    {
        if ($_POST) {
            // 检查账号是否存在
            $username = $this->request->get('username', 'string');
            $user = $this->userModel->findFirst(array(
                "conditions" => "username = :username:",
                "columns"    => "id",
                "limit"      => 1,
                "bind"       => array("username" => $username),
            ));
            if ($user) {
                Utils::tips('failed', '账号已存在', '/user/new');
            }

            // 整理 TODO :: 默认空密码问题
            $this->userModel->username = $username;
            $this->userModel->password = password_hash($this->request->get('password', 'string'), PASSWORD_DEFAULT);
            $this->userModel->name = $this->request->get('name', 'string');
            $this->userModel->mobile = $this->request->get('mobile', 'string');
            $this->userModel->status = $this->request->get('status', 'int', '0');
            $this->userModel->create_time = date('Y-m-d H:i:s');

            if (!$username || !$this->userModel->name) {
                Utils::tips('failed', '数据不完整', '/user/new');
            }
            // 存储
            try {
                if (!$this->userModel->create()) {
                    throw new \Exception('failed');
                }
            } catch (\Exception $e) {
                Utils::tips('failed', '操作失败', '/user');
            }
            Utils::tips('success', '操作成功', '/user');
        }
    }


    public function modifyAction()
    {
        $id = $this->request->get('id');
        if (!$id) {
            Utils::tips(null, 'Invalid Id');
        }

        $user = $this->userModel->findFirst($id);
        if (!$user) {
            Utils::tips(null, 'No Record Found');
        }

        if ($_POST) {
            if ($id == 1000 && $id != $this->_user_id) {
                Utils::tips('failed', '无权操作', '/user');
            }
            $user->name = $this->request->get('name', 'string');
            $user->mobile = $this->request->get('mobile', 'string');
            $user->status = $this->request->get('status', 'int');
            $user->update_time = date('Y-m-d H:i:s');

            // 超级管理可更改密码
            $password = $this->request->get('password', 'string');
            if ($password && $this->_user_id == 1000) {
                $user->password = password_hash($password, PASSWORD_DEFAULT);
            }

            // 修改权限
            $new_role_data = $this->request->get('role', 'int');
            $new_game_data = $this->request->get('game', 'int');
            $new_channel_data = $this->request->get('channel', 'int');
            $this->roleModel->modifyAuth('role_user', $id, $new_role_data);
            $this->roleModel->modifyAuth('user_game', $id, $new_game_data);
            $this->channelsModel->modifyAuth('user_channel', $id, $new_channel_data);
            // 存储
            $user->save();
            Utils::tips('success', '操作成功', '/user');
        }

        if (!$user->avatar) {
            $user->avatar = $this->userModel->getUserAvatar($user->username);
        }

        // 角色
        $role_list = $this->roleModel->find("status=1");
        $this->view->role_list = empty($role_list) ? '[]' : $role_list->toArray();
        $this->view->role_self = $this->roleModel->getResourceByID('role_user', 'role_id', $id) ?: null;

        // 游戏
        $this->view->game_list = $this->gameModel->getList();
        $this->view->game_self = $this->roleModel->getResourceByID('user_game', 'game_id', $id) ?: null;

        //渠道
        $this->view->channels = $this->channelsModel->getChannelsList();
        $this->view->channels_self = $this->channelsModel->getChannelByID('user_channel', 'channel_id', $id) ?: null;

        // common
        $this->view->is_supper_manager = $this->_user_id == 1000 ? true : false;
        $this->view->user = $user->toArray();
    }


    public function removeAction()
    {
        $id = $this->request->get('id');
        if (!$id || $id == 1000) {
            Utils::tips(null, 'Invalid Id');
        }
        if ($id == $this->_user_id) {
            Utils::tips(null, '不能删除当前登录账号');
        }
        $user = $this->userModel->findFirst($id);
        if ($user->delete() === false) {
            Utils::tips('failed', '操作失败', '/user');
        }
        Utils::tips('success', '操作成功', '/user');
    }

}