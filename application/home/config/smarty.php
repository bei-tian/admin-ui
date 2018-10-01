<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['css_path'] = '/static/home/css/';
$config['css_ver'] = '20130529';
$config['js_path'] = '/static/home/js/';
$config['js_ver'] = '20130820';
$config['img_path'] = '/static/home/';
$config['cache_lifetime'] = 600;
$config['template_dir'] = APPPATH.'views';
$config['cache_dir'] 	= FCPATH.'../tmp/smarty/home/caches';
$config['compile_dir'] 	= FCPATH.'../tmp/smarty/home/compiled';
$config['direct_output'] = false;//是否直接输出不缓存
$config['force_compile'] = false;//是否强制编译模版
$config['file_ext'] = '.html';
$config['caching']	= true;

/* End of file smarty.php */
/* Location: ./application/config/smarty.php */
