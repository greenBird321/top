<?php


namespace MyApp\Controllers;


use MyApp\Models\App;
use MyApp\Models\Game;
use MyApp\Models\Resource;
use MyApp\Models\Role;
use MyApp\Models\Utils;
use Phalcon\Mvc\Dispatcher;

class RoleController extends ControllerBase
{

    private $roleModel;
    private $gameModel;
    private $appModel;
    private $resourceModel;

    public function initialize()
    {
        parent::initialize();
        $this->roleModel = new Role();
        $this->gameModel = new Game();
        $this->appModel = new App();
        $this->resourceModel = new Resource();
    }


    public function indexAction()
    {
        $roles = $this->roleModel->find()->toArray();
        if ($roles) {
            foreach ($roles as $key => $value) {
                $roles[$key]['avatar'] = Utils::getAvatar($value['name']);
            }
        }
        $this->view->roles = $roles;
    }


    public function newAction()
    {
        if ($_POST) {
            $this->roleModel->name = $this->request->get('name', 'string');
            $this->roleModel->remark = $this->request->get('remark', 'string');
            $this->roleModel->status = $this->request->get('status', 'int', '0');
            $this->roleModel->create_time = date('Y-m-d H:i:s');

            if (!$this->roleModel->name) {
                Utils::tips('failed', '数据不完整', '/role/new');
            }

            // 存储
            try {
                if (!$this->roleModel->save()) {
                    throw new \Exception('failed');
                }
            } catch (\Exception $e) {
                Utils::tips('failed', '创建失败', '/role');
            }
            Utils::tips('success', '创建成功', '/role');
        }
    }


    public function modifyAction()
    {
        $id = $this->request->get('id');
        if (!$id) {
            Utils::tips(null, 'Invalid Id');
        }

        $role = $this->roleModel->findFirst($id);
        if (!$role) {
            Utils::tips(null, 'No Record Found');
        }


        if ($_POST) {
            $role->name = $this->request->get('name', 'string');
            $role->remark = $this->request->get('remark', 'string');
            $role->status = $this->request->get('status', 'int');
            $role->update_time = date('Y-m-d H:i:s');
            // TODO::修改权限
            $role->save();
            Utils::tips('success', '操作成功', '/role');
        }


        $role = $role->toArray();
        if (!isset($role['avatar'])) {
            $role['avatar'] = Utils::getAvatar($role['name']);
        }

        // 应用
        $this->view->app_list = $this->appModel->getList();

        // common
        $this->view->role = $role;
    }


    public function removeAction()
    {
        $id = $this->request->get('id');
        $role = $this->roleModel->findFirst($id);
        if ($role->delete() === false) {
            Utils::tips('failed', '删除失败', '/role');
        }
        Utils::tips('success', '删除成功', '/role');
    }

    public function resourceAction()
    {
        $id = $this->request->get('id', 'string');
        $role = $this->request->get('role', 'string');
        $role_id = $this->request->get('role_id', 'int');
        $data = $this->resourceModel->getResource($id);
        $data = $this->build_tree($data, 0);
        $role_resource = $this->roleModel->getResourceByID('role_resource', 'resource_id', $role_id) ?: null;
        $this->view->id = $id;
        $this->view->role = $role;
        $this->view->data = json_encode($data);
        $this->view->roleId = $role_id;
        $this->view->role_resource = json_encode($role_resource);

        $this->view->pick('role/resource');
        if ($_POST){
            $role_id = $this->request->get('role_id', 'int');
            $app = $_POST['app'];
            $resource_data = $_POST['resource'];
            $result = $this->roleModel->modifyRoleResource($app, $role_id, $resource_data);
            if (!$result){
                Utils::tips('error', '操作失败');
            }
            Utils::tips('success', '操作成功', "/role/modify?id={$role_id}");
        }
    }

    //寻找子类
    public function findChild(&$arr,$id){
        $childs = array();
        foreach ($arr as $k => $v){
            if($v['parent'] == $id){
                $childs[] = $v;
            }
        }
        return $childs;
    }


    function build_tree($rows,$root_id){
        $childs = $this->findChild($rows,$root_id);
        if(empty($childs)){
            return null;
        }
        foreach ($childs as $k => $v){
            //通过递归父类的id 来寻找它的子类
            $rescurTree = $this->build_tree($rows,$v['id']);
            if( null != $rescurTree){
                $childs[$k]['children']=$rescurTree;
            }
        }
        return $childs;
    }
}