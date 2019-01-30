<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Controller extends CI_Controller {
	public function __construct() {
		parent::__construct();

	}
}

class Common extends MY_Controller {
	public function __construct() {
		parent::__construct();

        $this->view->assign('base_url',  $this->router->directory.$this->router->fetch_class());
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


/**
 * 数据库基本增删查改
 */
class Curd extends Common
{
    protected $conf = [];
    protected $tableName = '';

    public function __construct() {
        parent::__construct();
        $this->tableName = $this->router->class;

    }


    /**
     * 列表页
     */
    function index() {
        $conf = [];
        $args = func_get_args();
        if ($args) {
            $conf = $args[0];
        }

        $get = $this->input->get();
        $page = $get['page'];
        $conf = $conf ? $conf : $this->conf['index'];

        $data = [];

        //开始钩子函数
        if ($conf['startFunc']) {
            $conf['startFunc']($data);
        }

        //额外数据|二维数组
        if ($conf['extra_data']) {
            $extra_data = $this->extra_data($conf['extra_data']);
            $data = array_merge($data, $extra_data);
        }


        //自定义表名
        if ($conf['tableName']) {
            $this->tableName = $conf['tableName'];
        }

        $query = $this->db->from($this->tableName);

        //join | 二维数组无键值数组
        if ($conf['join']) {
            foreach ($conf['join'] as $item) {
                $query->join($item[0], $item[1], $item[2]);
            }
        }


        //搜索条件 | 一维无键值数组
        if ($conf['search']) {
            $data['search'] = $search = $conf['search'];
            foreach ($search as $value) {
                $preg_alias = '';
                if (strpos($value, '.') !== false) {
                    list($preg_alias, $value) = explode('.', $value, 2);
                    $preg_alias .= '.';
                }
                $arr = explode('-', $value);
                $searchField = $preg_alias . $arr[0];
                $searchType = $arr[1];
                if (!$searchType) $searchType = '=';
                if ($get[$value] or $get[$value] === '0') {
                    if ($searchType == 'like') $get[$value] = '%' . $get[$value] . '%';
                    if ($arr[2] == 'time') {//转换时间为时间截
                        $get[$value] = strtotime($get[$value]);
                    }
                    if ($arr[2] == 'date') {//转换日期为时间截
                        if ($searchType == '<' or $searchType == '<=') {
                            $get[$value] = strtotime($get[$value]) + 86400; //结束日期需加一天时间
                        } else {
                            $get[$value] = strtotime($get[$value]);
                        }
                    }
                    $query->where([$searchField . " " . $searchType => $get[$value]]);
                }
            }
        }


        //where  | 一维有键值数组
        if ($conf['where']) {
            foreach ($conf['where'] as $key => $val) {
                if (is_array($val)) {
                    $query->where_in($key, $val);
                } else {
                    $query->where($key, $val);
                }
            }
        }
        //or_where  | 一维有键值数组
        if ($conf['or_where']) {
            $query->or_where($conf['or_where']);
        }


        /**
         * 'fields'=>[
         * 'B'=>['id','name'],
         * 'A'=>['alias_name'=>'real_name','b'],
         * ],
         */
        if (isset($conf['fields']) && $conf['fields']) {
            $field = [];
            foreach ($conf['fields'] as $k => $fs) {
                foreach ($fs as $_fk => $f) {
                    if (!is_numeric($_fk)) {
                        $f .= ' AS ' . $_fk;
                    }
                    if ($k) {
                        $field[] = $k . '.' . $f;
                    } else {
                        $field[] = $f;
                    }
                }
            }
            $field = join(',', $field);
            $query->select($field);
        } else {
            //field
            $field = '*';
            if ($conf['field']) {
                if (substr($conf['field'], 0, 1) == ',') {
                    $field .= $conf['field'];
                } else {
                    $field = $conf['field'];
                }
            }
            $query->select($field);
        }


        //order
        if ($conf['order']) {
            $query->order_by($conf['order']);
        }

        //group
        if ($conf['group']) {
            $query->group_by($conf['group']);
        }


        //pageSize
        if (isset($conf['pageSize'])) {
            $pageSize = $conf['pageSize'];
        } elseif (isset($this->loginUser['pagesize']) && $this->loginUser['pagesize'] > 0) {
            $pageSize = $this->loginUser['pagesize'];
        }

        if (empty($pageSize)) $pageSize = 10;
        if ($page <= 0) $page = 1;

        //是否显示总条数
        if($conf['pageSize'] != 'all') {
            $totalQuery = clone $query;
            $count = $totalQuery->count_all_results(null,false);
            $data['count'] = $count;
            $query->limit($pageSize,($page-1)*$pageSize);
        }


        $list = $query->get()->result_array();
//		echo $query->last_query()."<br >";
//		print_r($data);

        $data['pagesize'] = $pageSize;
        $data['page'] = $page;


        //循环处理的钩子
        if ($conf['itemFunc']) {
            $listTmp = [];
            foreach ($list as $item) {
                $conf['itemFunc']($item);
                $listTmp[] = $item;
            }
            $list = $listTmp;
        }

        $data['list'] = $list;


        //view
        if ($conf['view']) {
            $view = $conf['view'];
        }


        //结束钩子函数
        if ($conf['endFunc']) {
            $conf['endFunc']($data);
        }


        /*
		 * 使用公共模板生成列表页
		   'template' => [
                'cols'=>'', //layui table的表头，
		        'area' => ['500px','300px'], //添加数据弹窗大小
		        'searchHtml'=>'', //搜索表单插入额外html
                'html'=>'', //附加自定义html，可以加入laytpl模板、js，引入文件, 也可载入文件$this->load->view('fx/setting/level_rate.html', '', TRUE)
                'search'=>'hide', //是否隐藏搜索栏
                'add'=>'hide', //是否隐藏添加按钮
            ]
		*/
        if($conf['template']) {
            $data['template'] = $conf['template'];
            if ($this->input->is_ajax_request()) {
                $json['code'] = 0;
                $json['count'] = $data['count'];
                $json['data'] = $data['list'];
                $this->view->json($json);
            }
            $this->display('template/index', $data);
        } else {
            $this->display($view, $data);
        }
    }


    /**
     * 添加和修改数据的公共处理方法
     * @return string
     */
    function add() {
        $conf = [];
        $args = func_get_args();
        if ($args) {
            $conf = $args[0];
        }
        $conf = $conf ? $conf : $this->conf['add'];
        //自定义表名
        if ($conf['tableName']) {
            $this->tableName = $conf['tableName'];
        }

        //保存
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->save();
            exit;
        }

        $pk = !empty($conf['PK']) ? $conf['PK'] : 'id';

        //表单信息--------------------
        $id = $this->input->get($pk);
        if (isset($conf['idFunc']) && $id) {
            $conf['idFunc']($id);
        }

        $data = [];
        //开始钩子函数
        if ($conf['startFunc']) {
            $conf['startFunc']($data);
        }

        //额外数据
        if ($conf['extraData']) {
            $extra_data = $this->extra_data($conf['extraData']);
            $data = array_merge($data, $extra_data);
        }

        if ($id) {
            $query = $this->db->from($this->tableName);
            $query->where([$pk => $id]);
            //join | 二维数组
            if ($conf['join']) {
                foreach ($conf['join'] as $item) {
                    $query->join($item[0], $item[1], $item[2]);
                }
            }
            $data['item'] = $query->get()->row_array();
        }


        //自定义模板文件
        if ($conf['view']) {
            $view = $conf['view'];
        }
        //结束钩子函数
        if ($conf['endFunc']) {
            $conf['endFunc']($data);
        }

        /*
             * 使用公共模板生成表单页
               'template' => [
                    'action'=> '/XXX/save', //表单提交地址
                    'fields'=> [
                        ['field'=>'name','title'=>'名称','verify'=>'required','type'=>'text'],
                        ['field'=>'status','title'=>'状态','verify'=>'required','type'=>'radio','options'=>[
                            ['value'=>'1','title'=>'启用'],
                            ['value'=>'0','title'=>'停用']
                        ]],
                        ['title'=>'模板名','type'=>'tpl','tpl'=>function(&$item) { //自定义模板，$item为表单数据
                            return '<input type="text"  name="test-demo" value="'.$item['name'].'"  class="layui-input">'; //
                        }],
                        //支持类型，text,textarea,hidden,upload,radio,checkbox,select,tpl
                    ]
                ]
            */
        if($conf['template']) {
            $data['template'] = $conf['template'];
            if(!$data['template']['action']) {
                $data['template']['action'] = '/'.$this->router->directory.$this->router->fetch_class().'/save';
            }
            $this->display('template/add', $data);
        } else {
            $this->display($view, $data);
        }
    }


    function save() {
        $post = $this->input->post();
        //开始钩子函数
        if ($this->conf['save']['startFunc']) {
            $this->conf['save']['startFunc']($post);
        }

        /*
        //去掉关联表的字段 *二维数组*
        [
            ['plat_headhunter_contact'=> [
                                            'FK'=>'id',   关联主表id的字段
                                            'fields'=>[
                                                        'f_name',   相关字段
                                                       ]
                                        ],
            ]
        ]
        */
        $relate_data = [];
        if ($this->conf['save']['relateTable']) {
            foreach ($this->conf['save']['relateTable'] as $arr) {

                foreach ($arr as $_t => $_r) {
                    $relate_data[$_t]['id'] = $_r['FK'];
                    foreach ($_r['fields'] as $_f) {
                        $relate_data[$_t]['data'][$_f] = $post[$_f];
                        unset($post[$_f]);
                    }
                }
            }
        }

        //保存数据
        $pk = !empty($this->conf['save']['PK']) ? $this->conf['save']['PK'] : 'id';
        $id = $post_id = (int)$post[$pk];
        if ($post_id) {
            D($this->tableName)->save($post, [$pk => $post_id]);
        } else {
            $id = D($this->tableName)->add($post);
        }

        //保存关联表的数据
        if ($relate_data) {
            foreach ($relate_data as $_table => $_row) {
                if ($post_id) {
                    $exist = D($_table)->get_row([$_row['id'] => $post_id]);
                    if ($exist) { //关联表中对应的数据不存在，直接添加
                        D($_table)->save($_row['data'], [$_row['id'] => $post_id]);
                        continue;
                    }
                    $id = $post_id;
                }
                $_row['data'][$_row['id']] = $id;
                D($_table)->add($_row['data']);
            }
        }


        //结束钩子函数
        if ($this->conf['save']['endFunc']) {
            $this->conf['save']['endFunc']($id);
        }


        $this->saveOk();
    }


    //快速修改某个表的特定字段
    function setValue() {
        $set = $this->input->post("set");
        //刷新时间
        $key = key($set[1]);
        if ($set[1][$key] == 'time()') {
            $set[1][$key] = time();
        }

        if (D($set[0])->save($set[1], $set[2])) {
            echo 'ok';
        } else {
            echo '操作失败';
        }
    }

    /**
     * 删除
     * @return string
     */
    function del() {
        $id = $this->input->get('id');
        //开始钩子函数
        if ($this->conf['del']['startFunc']) {
            $this->conf['del']['startFunc']($id);
        }
        if (D($this->tableName)->del(['id' => $id])) {
            //删除成功后的结束钩子函数
            if ($this->conf['del']['endFunc']) {
                $this->conf['del']['endFunc']($id);
            }
            echo 'ok';
        } else {
            echo '删除失败';
        }
    }


    /**
     * 获取其它额外数据的函数
     * @param $config
     * @return array
     */
    protected function extra_data($config) {
        $extra_data = [];
        foreach ($config as $arr) {
            if ($arr['table']) {
                $where = $arr['where'] ? $arr['where'] : [];

                $extra_data[$arr['table']] = $this->db
                    ->from($arr['table'])
                    ->select($arr['field'])
                    ->where($where)
                    ->order_by($arr['order'])
                    ->limit($arr['limit'])
                    ->get()
                    ->result_array();
            }
        }
        return $extra_data;
    }
}


class Admin extends Curd {
    function __construct() {
        parent::__construct();
        session_start();
        if(!$_SESSION['admin_id']) {
            header("Location:/login");
            die;
        }

        //验证操作权限
        $url = $controller = $this->router->class;
        if($this->router->directory) $url = '/'.$this->router->directory.$url;
        $url = '/'.trim($url, '/').'/';
        if(!in_array($controller,['home'])) {
            $privilege = D("admin_role")->getValue($_SESSION['role_id'],'privilege');
            $menu = D("menu")->getOne(['url'=>$url]);

            if($menu and in_array($menu['id'],explode(',',$privilege))) {
                $arr = explode(',', $menu['privilege']);
                if($menu['privilege'] != '*' and !in_array($this->router->method,$arr)) {
                    echo '您无操作权限1！';
                    die;
                }
                //'有权限';
            } else {
                echo '您无操作权限2！';
                die;
            }
        }
    }
}


// END Controller class

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */
