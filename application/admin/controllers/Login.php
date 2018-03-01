<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends Common {
	public function __construct() {
		parent::__construct();
	}

	function index() {
	    if($this->is_post()) {

            $post = $this->post();
            $account = $post['account'];
            $password = md5(md5($post['password'])."DI389K23K21K403L2GS2");

            $item = D("admin")->getOne(['account'=>$account,'password'=>$password]);
            if($item) {
                session_start();
                $_SESSION['admin_id'] = $item['id'];
                $_SESSION['admin_account'] = $item['account'];
                $_SESSION['role_id'] = $item['role_id'];

                $this->success();
            } else {
                $this->error('帐号或密码错误');
            }
        } else {
            $this->display();
        }
	}

	function logout(){
		session_destroy();
		header("location:/login");
	}

}
