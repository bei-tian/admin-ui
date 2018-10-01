<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Info extends Curd {
	public function __construct() {
		parent::__construct();

		$this->conf['index'] = [
            'search'=> ['title-like'],
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
