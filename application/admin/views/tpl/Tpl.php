<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tpl extends Curd {
	public function __construct() {
		parent::__construct();

		$this->conf['index'] = [
            'search'=> ['title-like'],
		];
	}
}
