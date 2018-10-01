<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Info_cate extends Curd
{
    public function __construct()
    {
        parent::__construct();
        $this->conf['add'] = [
            'endFunc' => function (&$data) {
                $data['parent'] = D("info_cate")->getList(['parent_id' => 0]);
                if (!$data['item']['parent_id']) $data['item']['parent_id'] = (int)$this->get('parent_id');
            }
        ];

        $this->conf['index'] = [
            'where' => ['parent_id' => 0],
            'pageSize' => 100,
            'itemFunc' => function (&$item) {
                $item['sub'] = M("infoCate")->getTree($item['id']);
            }
        ];
    }

}
