<?php


namespace MyApp\Controllers;


use MyApp\Models\App;
use MyApp\Models\Resource;
use MyApp\Models\Utils;
use Phalcon\Mvc\Dispatcher;
use Xxtime\Util;

class ResourceController extends ControllerBase
{

    private $resourceModel;
    private $appModel;


    public function initialize()
    {
        parent::initialize();
        $this->resourceModel = new Resource();
        $this->appModel = new App();
    }


    public function indexAction()
    {
        $id = $this->request->get('id', 'string');
        if (!$id) {
            // 应用
            $this->view->app_list = $this->appModel->getList();
        } else {
            $this->session->set('appName', $id);
            $resource = $this->resourceModel->getResource($id);
            $tree = $this->build_tree($resource,0);
            $this->view->pick("resource/index_modify");
            $this->view->tree = json_encode($tree);
            $this->view->id = $id;
        }
    }


    public function newAction()
    {
        $appName = $this->request->get('app', 'string');
        $parentInfos = $this->resourceModel->getResource($appName);
        $parentInfo = $this -> getTree($parentInfos);
        $this->view->pick("resource/new");
        $this->view->appName = $appName;
        $this->view->parentInfo = $parentInfo;

        if ($_POST) {
            $app = $this->request->get('app', 'string');
            $name = $this->request->get('name', 'string');
            $resource = $this->request->get('resource', ['trim', 'string']);
            $parent = $this->request->get('parent', 'string');
            $icon = $this->request->get('icon', 'string');
            $status = isset($_POST['status']) ? 1 : 0;
            $type = $this->request->get('type', 'string');
            $remark = $this->request->get('remark', 'string');
            if (empty($name)) {
                Utils::tips('error', '名称必须填写', "/resource/new?app=$app");
            }
            $data = array(
                "app" => $app,
                "name" => $name,
                "parent" => $parent,
                "resource" => $resource,
                "type" => $type,
                "icon" => $icon,
                "status" => $status,
                "remark" => $remark,
                "create_time" => date('Y-m-d H:i:s')
            );
            $result = $this->resourceModel->createResource($data);
            if (!$result) {
                Utils::tips('error', '新建失败', "/resource/new?app=$app");
            }
            Utils::tips('success', '新建成功', "/resource/index?id=$app");
        }
    }


    public function modifyAction()
    {
        $id = $this->request->get('id', 'string');
        $data = $this->resourceModel->getResourceDetail($id);
        $this->view->pick("resource/modify");
        $this->view->id = $id;
        $this->view->data = $data;
        if ($_POST) {
            $id = $this->request->get('id', 'int');
            $app = $this->request->get('app', 'string');
            $name = $this->request->get('name', 'string');
            $resource = $this->request->get('resource', ['trim', 'string']);
            $type = $this->request->get('type', 'string');
            $sort = $this->request->get('sort', 'int');
            $icon = $this->request->get('icon', 'string');
            $status = empty($this->request->get('status', 'int')) ? 0 : $this->request->get('status', 'int');
            $remark = $this->request->get('remark', 'string');

            $data = array(
                'id' => $id,
                'app' => $app,
                'name' => $name,
                'resource' => $resource,
                'type' => $type,
                'sort' => $sort,
                'icon' => $icon,
                'status' => $status,
                'remark' => $remark
            );
            //更新数据
            $result = $this->resourceModel->modifyResource($data);
            if (!$result) {
                Utils::tips('error', '操作失败');
            }
            Utils::tips('success', '操作成功', "/resource/index?id=$app");
        }
    }


    public function removeAction()
    {
        $id = $this->request->get('id', 'int');
        $app = $this->session->get('appName');
        $result = $this->resourceModel->removeResource($id);
        if (!$result) {
            Utils::tips('error', '删除失败');
        }
        Utils::tips('success', '删除成功', "/resource/index?id=$app");
    }

    public function treeSerializeAction()
    {
        if ($_POST) {
            $serialize = $_POST['data'];
            $serialize = json_decode($serialize, true);
            $data = $this->analysisArray($serialize);
            $sort = $this->treeToArray($serialize);
            //组装数据
            $j = 0;
            for ($i = count($sort)-1; $i >= 0; $i--) {
                //进行数据的排序
                $result = $this->resourceModel->updateSort($sort[$j], $i);
                $j++;
                if (!$result) {
                    dd('排序更新失败');
                }
            }
            //更新父类
            foreach ($data as $parent => $children) {
                //fixed 代表的是没有做任何子菜单操作的,可直接更新
                if ($parent == 'fixed') {
                    foreach ($children as $item => $items) {
                        $update = $this->resourceModel->updateParent($items, 0);
                        if (!$update) {
                            dd('没有子类条目更新失败');
                        }
                    }
                }
                else {
                    //父类
                    $update = $this->resourceModel->updateParent($parent, 0);
                    if (!$update) {
                        dd('父类更新失败');
                    }
                    foreach ($children as $index => $childrenID) {
                        //第二层
                        if (!is_array($childrenID)) {
                            $updateSecond = $this->resourceModel->updateParent($childrenID, $parent);
                            if (!$updateSecond) {
                                dd('第二层更新数据库失败');
                            }
                        }
                        else {
                            //第三层
                            foreach ($childrenID as $j => $third) {
                                $updateThird = $this->resourceModel->updateParent($third, $index);
                                if (!$updateThird) {
                                    dd('第三层更新数据库失败');
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function object2array(&$object) {
        $object =  json_decode( json_encode( $object),true);
        return  $object;
    }

    //拼接数据
    /* 数据结构
    * array:2 [
    *       第一层
    *     1 => array:3 [
    *       第二层
    *        0 => 5
    *        1 => 2
    *        第三层
    *        2 => array:2 [
    *          0 => 3
    *          1 => 4
    *        ]
    *      ]
    *      "fixed" => array:3 [
    *        0 => 6
    *        1 => 7
    *        2 => 8
    *      ]
    *    ]
     */
    public function analysisArray($param)
    {
        foreach ($param as $key => $value) {
            if (isset($value['children'])) {
                foreach ($value['children'] as $k => $v) {
                    $result[$value['id']][] = $v['id'];
                    if (isset($v['children'])){
                        foreach ($v['children'] as $index => $childrenId){
                            $result[$value['id']][$v['id']][] = $childrenId['id'];
                        }
                    }
                }
            }
            else {
                $result['fixed'][] = $value['id'];
            }
        }
        return $result;
    }

    public function treeToArray($param)
    {
        foreach ($param as $value){
            $newArr[] = $value['id'];
            if (!empty($value['children'])){
                foreach ($value['children'] as $item){
                    $newArr[] = $item['id'];
                    if (!empty($item['children'])){
                        foreach ($item['children'] as $v){
                            $newArr[] = $v['id'];
                        }
                    }
                }
            }
        }
        return $newArr;
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

    /**
     * des：获取父级的无限分类
     * @param $resource [type|array] 查询的所有数据
     * @param $parent [type|int] 父级id
     * @param $result [type|array] 空数组
     * @param $spac [type|int] 占位符
     * @return array
     *
     */
    private function getTree( $resource, $parent=0, &$result=array(),$spac=0 )
    {
        $spac += 2;
        $results =array();
        foreach($resource as $key => $value )
        {
            if($value['parent'] == $parent )
            {
                $results[] =  $value;
            }
        }

        foreach( $results as $k => $v )
        {
            $v['name'] = str_repeat('&nbsp;',$spac).'|--'.$v['name'];
            $result[] = $v;
            $this -> getTree($resource,$v['id'],$result,$spac);
        }

        return $result;

    }
}