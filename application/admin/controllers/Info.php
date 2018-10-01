<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Info extends Curd {
	public function __construct() {
		parent::__construct();

		$this->conf['index'] = [
            'search'=> ['account-like'],
		];

		$this->conf['add'] = [
			'endFunc' => function (&$data) {
				$data['cate_list'] = D("info_cate")->getList();
			}
		];

	}

}
