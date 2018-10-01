<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class InfoCate_model extends CI_Model {

	function getTree($parent_id, $field='*') {
	    $where = [
            'parent_id'=>$parent_id,
        ];
		$list = D('info_cate')->getList($where, $field);
		$out = [];
		foreach ($list as $item) {
			$sub = $this->getTree($item['id'], $field);
			if($sub) {
                $item['sub'] = $sub;
            }
			$out[] = $item;
		}
		return $out;
	}

}
