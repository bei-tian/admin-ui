<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu_model extends CI_Model {

	//递归得到所有栏目数据
	function getTree($parent_id, $field='*', $privilege=false) {
	    $where = [
            'parent_id'=>$parent_id,
            'is_menu'=>1
        ];
	    if($privilege) {
            $privilege = D("admin_role")->getValue($_SESSION['role_id'],'privilege');
            $where['id in'] = explode(',',$privilege);
        }
		$list = D('menu')->getList($where, $field, 'sort asc');
		$out = [];
		foreach ($list as $item) {
			$sub = $this->getTree($item['id'], $field, $privilege);
			if($sub) {
                $item['sub'] = $sub;
            }
			$out[] = $item;
		}
		return $out;
	}

}
