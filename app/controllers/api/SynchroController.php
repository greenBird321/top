<?php
/**
 * Created by PhpStorm.
 * User: lkl
 * Date: 2017/11/10
 * Time: 14:01
 */

namespace MyApp\Controllers\Api;

use MyApp\Models\Classes;
use MyApp\Models\Game;

class SynchroController extends ControllerBase
{
    private $classModel;


    private $gameModel;

    public function initialize()
    {
        $this->classModel = new Classes();
        $this->gameModel = new Game();
    }

    public function indexAction()
    {
        $secret_key = $secret_key = $this->config->setting->secret_key;

        $key = trim($this->request->get('key', 'string'));
        $time = trim($this->request->get('time', 'string'));

        if($key != md5($secret_key.$time)){
            $this->response->setJsonContent(['code' => 1, 'data' => 'key failed'])->send();
            exit();
        }

        $classes = $this->classModel->getAllClass();

        foreach ($classes as $info) {
            $game = $this->gameModel->getGame($info['id']);
            $info['game'] = $game;
            $gameList[] = $info;
        }

        $this->response->setJsonContent(['code' => 0, 'data' => $gameList])->send();
        exit();
    }
}