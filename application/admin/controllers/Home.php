<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Admin {
	function index() {
        $data['account'] = $_SESSION['admin_account'];
        $this->display($data);
        $this->display();
	}

	//导航栏数据接口
	function nav() {
        $this->load->model('Menu_model');
        $nav = $this->Menu_model->getTree(0,'id,name,icon,url as href',true);
        $this->success($nav);
    }

    function setTheme() {
	    $theme = $this->get('theme','default');
        setcookie("theme", $theme, time()+30*86400,'/');
        echo $_COOKIE['theme'];
    }
}
