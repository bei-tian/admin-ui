<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Readme extends Admin_Controller {

    public function __construct() {
        parent::__construct();

        $this->tableName = 'fx_level';

        //默认增删除查改操作
        $this->conf['index'] = [
            'template' => [
                'cols'=>"[[
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', width:60},
                    {field: 'name', title: '名称'},
                    {field: 'create_time', title: '创建时间',width:180},
                    {fixed: 'right',title: '操作', width:120, toolbar: '#operator'}
                ]]",
                'area' => ['500px','300px']
            ],
            'search' => ['name-like'],
        ];

        //自定义操作
        $this->conf['index'] = [
            'template' => [
                'cols'=>"[[ //表头
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', width:60},
                    {field: 'name', title: '名称'},
                    {field: 'create_time', title: '创建时间',width:180},
                    {fixed: 'right',title: '操作', width:150, align:'center', toolbar: '#operator2'}
                ]]",
                'area' => ['500px','300px'],
                'html'=>'<script type="text/html" id="operator2">
    <a class="layui-btn layui-btn-xs" lay-event="verify">审核</a>
</script>
<script>
    layui.use(["table"], function(){
        var $ = layui.jquery;
        var table = layui.table;
        //审核
        table.on("tool(table)", function(obj){
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event;
            console.log(obj);
        });
    });
</script>'
            ],
            'search' => ['name-like'],
        ];


        //自定义表单页模板操作
        $this->conf['add'] = [
            'template'=>[
                'fields'=> [
                    ['field'=>'id','type'=>'hidden'],
                    ['field'=>'name','title'=>'名称','verify'=>'required','type'=>'text'],
                    ['title'=>'模板名','type'=>'tpl','tpl'=>function(&$item) {
                        return '<input type="text"  name="test-tpl" value="'.$item['name'].'"  class="layui-input">';
                    }],
                ]
            ],

            ''
        ];


    }



}
