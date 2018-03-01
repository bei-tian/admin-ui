<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends Admin {
    public function __construct() {
        parent::__construct();
        $this->tableName = 'admin';

        $this->conf = [
            'index'=> [
                'search'=> ['account-like'],
            ],
            'add'=>[
                'endFunc'=>function(&$data) {
                    $data['role'] = D("admin_role")->getList();
                }
            ],
            'save'=>[
                'startFunc'=>function(&$post) {
                    if($post['password']) {
                        $post['password'] = md5(md5($post['password'])."DI389K23K21K403L2GS2");
                    } else {
                        unset($post['password']);
                    }
                }
            ]
        ];
    }

}
