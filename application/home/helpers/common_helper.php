<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 获取公共model，来操作数据库
 * @param $table
 * @return mixed
 */
function D($table) {
    static $model = null;
    if ($model === null)
        $model = new MY_model();

    return $model->setTable($table);
}

/**
 * 获取指定model，必须先定义model
 * @param $table
 * @return mixed
 */
function M($table){
    $CI =& get_instance();
    $model = $table.'_model';
    $CI->load->model($model);

    return $CI->$model;
}

/**
 * 快速获取数据库中某个表中的指定字段的值
 * 主要用于模板中单个值关联查找，避免连表
 * @param $id
 * @param $table
 * @param $field
 * @return mixed
 */
function getValue($id,$table,$field,$idName='id') {
    $result = D($table)->getOne([$idName=>$id],$field);
    return $result[$field];
}



function configItem($key,$configFile='') {
    $CI =& get_instance();
    if($configFile) {
        $CI->load->config($configFile);
    }
    return $CI->config->item($key);
}