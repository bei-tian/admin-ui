<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends Admin {
    public function __construct() {
        parent::__construct();
        $this->tableName = 'admin_role';

        $this->conf = [
            'index'=> [
                'search'=> ['name-like'],
            ],
        ];
    }


    function privilege() {
        $id = $this->input->get('id');
        if($this->is_post()) {
            $privilege = $this->input->post('privilege');
            $privilege = implode(',',$privilege);
            D('admin_role')->save(['privilege'=>$privilege],['id'=>$id]);

            //更新当前登陆用户的权限
            if($id == $_SESSION['group_id']) {
                $_SESSION['privilege'] = $privilege;
            }


            $this->saveOk();
        } else {
            $item = D('admin_role')->find($id);
            $privilege = explode(',', $item['privilege']);
            $nodes = $this->getTree(0,$privilege);
            $data['nodes'] = json_encode($nodes);

            $this->display($data);
        }
    }

    function getTree($parent_id,$privilege) {
        $list = D('menu')->getList(['parent_id'=>$parent_id], 'id,name,id as checkboxValue', 'sort asc');
        $out = [];
        foreach ($list as $item) {
            $children = $this->getTree($item['id'],$privilege);
            if($children) {
                $item['children'] = $children;
            }
            $item['spread'] = true;
            if(in_array($item['id'],$privilege)) {
                $item['checked'] = true;
            }

            $out[] = $item;
        }
        return $out;
    }

}
