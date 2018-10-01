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


/**
 * 转换数据库中整型数值对象某个特定含义值,如：0男，1女
 * @param $case
 * @param $index
 * @return string
 */
function dbCase($case,$index){
    $CI =& get_instance();
    $CI->load->config('dbcase');
    $caseArr = $CI->config->config[$index];
    $value = $caseArr[$case];
    $arr = explode('|',$value);
    if($arr[1]) {
        $value = '<font color="'.$arr[1].'">'.$arr[0].'</font>';
    } else {
        $value = $arr[0];
    }

    return $value;
}

/**
 * 将数据库中整型数值对象配置数据生成相应的表单元素
 * @param $case
 * @param $configKey
 * @param string $type
 * @param string $name
 * @param string $defaultOption
 * @return string
 */
function dbCaseForm($case,$configKey,$type='select',$name='',$defaultOption='请选择'){
    $CI =& get_instance();
    $CI->load->config('dbcase');
    $caseArr = $CI->config->config[$configKey];
    $name = $name ? $name : $configKey;

    $result = '';


    //下拉列表
    if($type == 'select') {
        $result = '<select name="'.$name.'" lay-filter="'.$name.'" class="form-item" >';
        $result .= '<option value="">'.$defaultOption.'</option>';
        foreach ($caseArr as $key=>$value) {
            $arr = explode('|',$value);
            $value = $arr[0];
            $color = $arr[1];
            $selected = ($key == $case) ? 'selected':'';
            if($case == null) $selected = '';
            $result .= '<option value="'.$key.'" color="'.$color.'" '.$selected.'>'.$value.'</option>';
        }
        $result .= '</select>';
    }

    //单选
    if($type == 'radio') {
        foreach ($caseArr as $key=>$value) {
            $arr = explode('|',$value);
            $value = $arr[0];
            $color = $arr[1];
            $checked = ($key == $case && !is_null($case)) ? 'checked':'';
            $result .= '<input type="radio" name="'.$name.'" value="'.$key.'" color="'.$color.'" title="'.$value.'" class="form-item" '.$checked.'>';
        }
    }

    return $result;
}



//将数据库中的数据转换成下拉列表
function dbFormSelect($cur,$name,$table,$valueField,$contentField,$where=[],$defaultOption='请选择'){
    $list = M($table)->getList($where,"$valueField,$contentField");

    $result = '<select name="'.$name.'" lay-filter="'.$name.'" class="form-item" >';
    $result .= '<option value="">'.$defaultOption.'</option>';
    foreach ($list as $arr) {
        $selected = ($arr[$valueField] == $cur) ? 'selected':'';
        $result .= '<option value="'.$arr[$valueField].'" '.$selected.'>'.$arr[$contentField].'</option>';
    }
    $result .= '</select>';

    return $result;
}


function configItem($key,$configFile='') {
    $CI =& get_instance();
    if($configFile) {
        $CI->load->config($configFile);
    }
    return $CI->config->item($key);
}