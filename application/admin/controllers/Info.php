<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Info extends Curd {
	public function __construct() {
		parent::__construct();

		$this->conf['index'] = [
            'search'=> ['cate_id','title-like'],
            'endFunc' => function (&$data) {
                $data['cate'] = M("infoCate")->getTree(0);
            },
            'itemFunc' => function (&$item) {
                $cate = D("info_cate")->find($item['cate_id']);
                if ($cate['parent_id'] > 0) {
                    $bigCate = D("info_cate")->find($cate['parent_id']);
                    $item['cate'] = $bigCate['name'].' > '.$cate['name'] ;
                } else {
                    $item['cate'] = $cate['name'];
                }
            },
		];
		$this->conf['add'] = [
			'endFunc' => function (&$data) {
				$data['cate'] = M("infoCate")->getTree(0);
			}
		];
        $this->conf['save'] = [
            'startFunc'=> function(&$post) {
                unset($post['file']);
            }
        ];
	}

}
