<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['css_path'] = '/static/admin/css/';
$config['css_ver'] = '20130529';
$config['js_path'] = '/static/admin/js/';
$config['js_ver'] = '20130820';
$config['img_path'] = '/static/admin/';
$config['cache_lifetime'] = 600;
$config['template_dir'] = APPPATH.'views';
$config['cache_dir'] 	= FCPATH.'../tmp/smarty/admin/caches';
$config['compile_dir'] 	= FCPATH.'../tmp/smarty/admin/compiled';
$config['direct_output'] = false;//是否直接输出不缓存
$config['force_compile'] = false;//是否强制编译模版
$config['file_ext'] = '.html';
$config['caching']	= true;

/* End of file smarty.php */
/* Location: ./application/config/smarty.php */
