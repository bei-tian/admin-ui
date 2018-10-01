<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends Admin {
    public function __construct() {
        parent::__construct();

        $this->conf['add'] = [
            'endFunc'=> function(&$data) {
                if(!$data['item']['icon']) $data['item']['icon'] = '&#xe6de;';
                if(!$data['item']['is_menu']) $data['item']['is_menu'] = 1;
                if(!$data['item']['parent_id']) $data['item']['parent_id'] = (int)$this->get('parent_id');
            }
        ];

        $this->conf['save'] = [
            'startFunc'=> function(&$post) {
                $post['is_menu'] = (int)$post['is_menu'];
                $post['url'] = '/'.trim($post['url'],'/').'/';
            }
        ];


        $this->conf['index'] = [
            'where'=>['parent_id'=>0],
            'endFunc'=> function(&$data) {
                $id = $this->get('id',$data['list'][0]['id']);
                $data['current'] = D("menu")->getOne(['id'=>$id]);
                $data['sub'] = M("menu")->getTree($id);
            }
        ];
    }

}
