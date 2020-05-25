<?php


namespace MyApp\Controllers;


use MyApp\Models\Game;
use MyApp\Models\Classes;
use Phalcon\DI;
use MyApp\Models\Lang;
use MyApp\Models\Utils;
use Phalcon\Mvc\Dispatcher;
use MyApp\Plugins\Validator;

class GameController extends ControllerBase
{

    private $gameModel;
    private $classesModel;
    private $langModel;
    protected $validator;

    public function initialize()
    {
        parent::initialize();
        $this->gameModel = new Game();
        $this->classesModel = new Classes();
        $this->langModel = new Lang();
        $this->validator = new Validator();
    }


    public function indexAction()
    {
        $this->view->games = $this->gameModel->getList();
    }


    public function newAction()
    {
        $gameClass = $this->classesModel->getAllClass();
        $gameVerison = $this->langModel->getGameLang();
        $this->view->pick("game/new");
        $this->view->gameclass = $gameClass;
        $this->view->gameverison = $gameVerison;

        if ($_POST) {
            $name = $this->request->get('name', 'string');
            $en_name = $this->request->get('en_name', 'string');
            $class_id = $this->request->get('class_id', 'string');
            $version = $this->request->get('version', 'string');
            $domain = $this->request->get('domain', 'string');
            $status = $this->request->get('status', 'int', '0');
            $create_time = date('Y-m-d H:i:s');
            $classId = $this->classesModel->getClassName($class_id);
            $rules = [
                [$name, 'isEmpty', '应用名称不能为空'],
                [$en_name, 'isEmpty', '英文名称不能为空'],
                [$class_id, 'isEmpty', '游戏分类不能为空'],
                [$version, 'isEmpty', '语言版本不能为空'],
                [$domain, 'isEmpty', '应用域名不能为空']

            ];
            $this->validator->setRule($rules)->run();

            $db = DI::getDefault()->get('dbBackend');

            $db->insertAsDict(
                "games",
                [
                    'name' => $name,
                    'en_name' => $en_name,
                    'class_id' => $classId,
                    'version' => $version,
                    'domain'  => $domain,
                    'create_time' => $create_time,
                    'status' => $status
                ]
            );

            $gameId = $db->lastInsertId();
            //拼接game_id
            $game_id = $class_id . $gameId;

            $result = $this->gameModel->updateGameID($gameId, $game_id);

            if (!$result) {
                Utils::tips(null, '操作失败', '/game');
            }

            Utils::tips('success', '操作成功', '/game');
        }
    }


    public function modifyAction()
    {
        $id = $this->request->get('id');
        if (!$id) {
            Utils::tips(null, 'Invalid Id');
        }

        $game = $this->gameModel->findFirst($id);
        if (!$game) {
            Utils::tips(null, 'No Record Found');
        }

        if ($_POST) {
            $game->name = $this->request->get('name', 'string');
            $game->en_name = $this->request->get('en_name', 'string');
            $game->class_id = $this->request->get('class_id', 'string');
            $game->version = $this->request->get('version', 'string');
            $game->domain = $this->request->get('domain', 'string');
            $game->status = $this->request->get('status', 'int');
            $game->save();
            Utils::tips('success', '操作成功', '/game');
        }

        $this->view->game = $game->toArray();
    }


    public function removeAction()
    {
        $id = $this->request->get('id');
        if (!$id) {
            Utils::tips(null, 'Invalid Id');
        }
        $game = $this->gameModel->findFirst($id);
        if ($game->delete() === false) {
            Utils::tips('failed', '操作失败', '/game');
        }
        Utils::tips('success', '操作成功', '/game');
    }

}