<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Readme extends Admin {

    public function __construct() {
        parent::__construct();

        $this->conf['fields'] = [
            'id' => ['type'=>'hidden','title'=>'ID'],
            'name' => ['type'=>'text', 'title'=>'名称', 'width'=>60, 'verify'=>'required'],
            'image' => ['type'=>'upload', 'title'=>'图片'],
            'remarks' => ['type'=>'textarea', 'title'=>'备注'],
        ];
        $this->conf['index'] = [
            'search' => ['name-like'],
            'template' => [
                'fields'=> ['id', 'name', 'create_time', 'operator'],
                'area' => ['800px','600px'],
            ],
        ];

        //自定义操作
        $this->conf['index'] = [
            'search' => ['name-like'],
            'template' => [
                'fields'=> ['id', 'name',  'create_time', 'operator'=>'#operator2'],
                'area' => ['800px','600px'],
                'html'=> $this->view->fetch('template/test')
            ],
        ];

        $this->conf['add'] = [
            'template'=>[
                'fields'=> $this->conf['fields']
            ]
        ];


    }



}
