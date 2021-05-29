<?php
class View
{


	function __construct()
	{
		$this->View();
	}

	function View()
	{

		$CI = & get_instance();
		$CI->load->library('smarty');
		$CI->config->load('smarty', TRUE);
		header('Content-type: text/html; charset='.$CI->config->item('charset'));
		$this->_template = $CI->smarty;
		$smarty_config = $CI->config->item('smarty');
		$this->_template->js_path = $smarty_config['js_path'];
		$this->_template->js_ver  = $smarty_config['js_ver'];
		$this->_template->css_path = $smarty_config['css_path'];
		$this->_template->css_ver  = $smarty_config['css_ver'];
		$this->_template->img_path = $smarty_config['img_path'];
		$this->_template->compile_dir = $smarty_config['compile_dir'];
		$this->_template->template_dir = $smarty_config['template_dir'];
		$this->_template->caching = $smarty_config['caching'];
		$this->_template->cache_dir 	= $smarty_config['cache_dir'];
		$this->_template->cache_lifetime = $smarty_config['cache_lifetime'];
		$this->_template->direct_output  =$smarty_config['direct_output'];
		$this->_template->force_compile =$smarty_config['force_compile'];
		$this->_template->file_ext =$smarty_config['file_ext'];
		$this->_template->base_path = FCPATH;
		$this->_template->output = $CI->output;

		if ($CI->input->get('c')=='cc')
		{
			$this->_template->caching = FALSE;
		}

		if ($CI->input->get('c')=='ccc')
		{
			$this->_template->caching = FALSE;
			$this->_template->force_compile = TRUE;
		}
	}

	function assign($var, $value = '')
	{
		$this->_template->assign($var, $value);
	}

	public function is_cached($file='', $cachekey='')
	{
		if ($cachekey)$cachekey=md5($cachekey);
		return $this->_template->is_cached($file . $this->_template->file_ext, $cachekey);
	}

	public function display($file, $cachekey = '')
	{
		if ($cachekey)$cachekey=md5($cachekey);
		$this->_template->display($file . $this->_template->file_ext,$cachekey);
	}

	public function output($file, $cachekey = '')
	{
		$this->display($file, $cachekey = '');
		$this->_template->output->_display();
	}

	public function fetch($file = null, $ext=null, $cachekey='')
	{
		if ($ext===null)
		{
			$ext = $this->_template->file_ext;
		}
		if ($cachekey)$cachekey=md5($cachekey);
		return $this->_template->fetch($file . $ext, $cachekey);
	}

	public function fetch_content($content, $cachekey='')
	{
		if ($cachekey)$cachekey=md5($cachekey);
		return $this->fetch('str:'.$content, '', $cachekey);
	}

	public function page($filename)
	{
		return $this->_template->page($filename);
	}

	function json($data)
	{
		echo json_encode($data);
		exit;
	}

}

?>
