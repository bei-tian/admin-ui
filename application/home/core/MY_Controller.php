<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Controller extends CI_Controller {
	public function __construct() {
		parent::__construct();

	}
}

class Common extends MY_Controller {
	public function __construct() {
		parent::__construct();

	}

	function display($view='',$data=[]) {
		if(is_array($view)){
			$data = $view;
			$view = $this->router->directory.$this->router->fetch_class()."/".$this->router->fetch_method();
		} elseif (empty($view)) {
			$view = $this->router->directory.$this->router->fetch_class()."/".$this->router->fetch_method();
		}
		$this->view->assign($data);
		$this->view->display($view);
	}

	/**
	 * 接收get参数,增加为空时的默认值
	 * @param null $index
	 * @param null $default
	 * @return null
	 */
	function get($index=null,$default=null) {
		$value = $this->input->get($index);
		$value = $value ? $value : $default;
		return $value;
	}

	/**
	 * 接收post参数,增加为空时的默认值
	 * @param null $index
	 * @param null $default
	 * @return null
	 */
	function post($index=null,$default=null) {
		$value = $this->input->post($index);
		$value = $value ? $value : $default;
		return $value;
	}

	function is_post() {
	    return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    //保存成功，关闭弹窗,并刷新父窗口
    function saveOk() {
        echo '<script>var $ = top.layui.jquery;$(".layui-show iframe")[0].contentWindow.location.reload();top.layui.layer.closeAll();</script>';
        exit();
    }

    function success($data=[]){
        $ret['code'] = 200;
        $ret['data'] = $data;
        $out = json_encode($ret,JSON_UNESCAPED_SLASHES);
        echo $out;
        die;
    }

    function error($msg){
        if (is_numeric($msg)) {
            $code = $msg;
            $this->config->load('error_code');
            $error_code = $this->config->item('error_code');
            $ret['code'] = $code;
            $ret['msg'] = $error_code[$code];
        } else {
            $ret['code'] = 500;
            $ret['msg'] = $msg;
        }
        echo json_encode($ret);
        die;
    }

}


// END Controller class

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */