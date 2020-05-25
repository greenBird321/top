<?php


namespace MyApp\Controllers;


use MyApp\Models\App;
use MyApp\Models\Utils;
use Phalcon\Mvc\Dispatcher;
use MyApp\Plugins\Validator;

class AppController extends ControllerBase
{

    private $appModel;
    protected $validator;


    public function initialize()
    {
        parent::initialize();
        $this->appModel = new App();
        $this->validator = new Validator();
    }


    public function indexAction()
    {
        $this->view->apps = $this->appModel->getList();
    }


    public function newAction()
    {
        if ($_POST) {
            $this->appModel->name = $name = $this->request->get('name', 'string');
            $this->appModel->en_name = $en_name = $this->request->get('en_name', 'string');
            $this->appModel->app_id = $app_id = $this->request->get('app_id', 'string');
            $this->appModel->version = $version = $this->request->get('version', 'string');
            $this->appModel->domain = $domain = $this->request->get('domain', 'string');
            $this->appModel->status = $this->request->get('status', 'int', '0');
            $this->appModel->create_time = date('Y-m-d H:i:s');
            $rules = [
                [$name, 'isEmpty', '应用名称不能为空'],
                [$en_name, 'isEmpty', '英文名称不能为空'],
                [$app_id, 'isEmpty', 'AppID不能为空'],
                [$version, 'isEmpty', '版本不能为空'],
                [$domain, 'isEmpty', '应用域名不能为空']

            ];
            $this->validator->setRule($rules)->run();
            if (!$this->appModel->save()) {
                Utils::tips('failed', '操作失败', '/app');
            }
            Utils::tips('success', '操作成功', '/app');
        }
    }


    public function modifyAction()
    {
        $id = $this->request->get('id');
        if (!$id) {
            Utils::tips(null, 'Invalid Id');
        }

        $app = $this->appModel->findFirst($id);
        if (!$app) {
            Utils::tips(null, 'No Record Found');
        }

        if ($_POST) {
            $app->name = $this->request->get('name', 'string');
            $app->en_name = $this->request->get('en_name', 'string');
            $app->version = $this->request->get('version', 'string');
            $app->domain = $this->request->get('domain', 'string');
            $app->status = $this->request->get('status', 'int');
            $app->save();
            Utils::tips('success', '操作成功', '/app');
        }

        $this->view->app = $app->toArray();
    }


    public function removeAction()
    {
        $id = $this->request->get('id');
        if (!$id) {
            Utils::tips(null, 'Invalid Id');
        }
        $app = $this->appModel->findFirst($id);
        if ($app->delete() === false) {
            Utils::tips('failed', '操作失败', '/app');
        }
        Utils::tips('success', '操作成功', '/app');
    }

}