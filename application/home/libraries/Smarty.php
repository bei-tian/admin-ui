<?php
class Smarty {
	var $template_dir   = '';
	var $cache_dir	  = '';
	var $compile_dir	= '';
	var $cache_lifetime = 3600; // 缓存更新时间, 默认 3600 秒
	var $direct_output  = false;
	var $caching		= false;
	var $template	   = array();
	var $force_compile  = false;

	var $_var		   = array();
	var $_echash		= '554fcae493e564ee0dc75bdf2ebf94ca';
	var $_foreach	   = array();
	var $_current_file  = '';
	var $_expires	   = 0;
	var $_errorlevel	= 0;
	var $_nowtime	   = null;
	var $_checkfile	 = true;
	var $_foreachmark   = '';
	var $_seterror	  = 0;

	var $_temp_key	  = array();  // 临时存放 foreach 里 key 的数组
	var $_temp_val	  = array();  // 临时存放 foreach 里 item 的数组

	var $js_path;
	var $css_path;
	var $base_path;
	var $base_url;

	var $output = '';

	function __construct()
	{
		$this->Smarty();
	}

	function Smarty()
	{
		$this->_errorlevel 	= error_reporting();
		$this->_nowtime		= time();
	}

	/**
	 * 注册变量
	 *
	 * @access  public
	 * @param   mix	  $tpl_var
	 * @param   mix	  $value
	 *
	 * @return  void
	 */
	function assign($tpl_var, $value = '')
	{
		if (is_array($tpl_var))
		{
			foreach ($tpl_var AS $key => $val)
			{
				if ($key != '')
				{
					$this->_var[$key] = $val;
				}
			}
		}
		else
		{
			if ($tpl_var != '')
			{
				$this->_var[$tpl_var] = $value;
			}
		}
	}

	private function get_cache_name($filename)
	{
		return trim(str_replace(dirname(APPPATH),'',$filename),'/');
	}

	private function get_compiled_name($filename)
	{
		return $this->compile_dir . '/' . trim(str_replace(dirname(APPPATH),'',$filename),'/').'.php';
	}

	/**
	 * 显示页面函数
	 *
	 * @access  public
	 * @param   string	  $filename
	 * @param   sting	  $cache_id
	 *
	 * @return  void
	 */
	function display($filename, $cache_id = '')
	{
		$this->_seterror++;
		error_reporting(E_ALL ^ E_NOTICE);
		$this->_checkfile = false;
		$out = $this->fetch($filename, $cache_id);
		if (strpos($out, $this->_echash) !== false)
		{
			$k = explode($this->_echash, $out);
			foreach ($k AS $key => $val)
			{
				if (($key % 2) == 1)
				{
					$k[$key] = $this->insert_mod($val);
				}
			}
			$out = implode('', $k);
			$out = str_replace(array('<!----|', '|---->'), '', $out);
		}

		$out = str_replace(array('taosafe.cn/','taosafe.cn"'), array('taosafe.cn:88/','taosafe.cn:88"'), $out);
		error_reporting($this->_errorlevel);
		$this->_seterror--;
		if ($this->output && is_object($this->output)){
			$this->output->set_output($out);
		}else{
			echo $out;
		}
	}

	public function page($filename)
	{
		$tmp = array_pop(explode('/', $filename));
		$path = FCPATH.'page/'.str_replace($tmp,md5($tmp),$filename).'.html';
		return file_exists($path) ? file_get_contents($path) : '';
	}

	/**
	 * 处理模板文件
	 *
	 * @access  public
	 * @param   string	  $filename
	 * @param   sting	  $cache_id
	 *
	 * @return  sring
	 */
	function fetch($filename, $cache_id = '')
	{
		$template_dir = $this->template_dir;
		if ($filename{0} == '@' || $filename{0} == '/')
		{
			$filename = substr($filename, 1);
			$template_dir = $this->base_template_dir;
		}
		if (!$this->_seterror)
		{
			error_reporting(E_ALL ^ E_NOTICE);
		}
		$this->_seterror++;

		if (strncmp($filename,'str:', 4) == 0)
		{
			$out = $this->_eval($this->fetch_str(substr($filename, 4)));
		}
		else
		{
			if ($this->_checkfile)
			{
				if (!file_exists($filename))
				{
					$filename = $template_dir . '/' . $filename;
					if (!file_exists($filename)) {
						throw new Exception('templete "'.$filename.'" no exists!');
					}
				}
			}
			else
			{
				$filename = $template_dir . '/' . $filename;
				if (!file_exists($filename)) {
					throw new Exception('templete "'.$filename.'" no exists!');
				}
			}

			if ($this->direct_output)
			{
				$this->_current_file = $filename;
				$out = $this->_eval($this->fetch_str(file_get_contents($filename)));
			}
			else
			{
				if ($cache_id && $this->caching)
				{
					$out = $this->template_out;
				}
				else
				{
					if (!in_array($filename, $this->template))
					{
						$this->template[] = $filename;
					}

					$out = $this->make_compiled($filename);
					if ($cache_id)
					{
						$cachename =  $this->get_cache_name($filename) . '_' . $cache_id;
						$data = serialize(array('template' => $this->template, 'expires' => $this->_nowtime + $this->cache_lifetime, 'maketime' => $this->_nowtime));
						$out = str_replace("\r", '', $out);
						while (strpos($out, "\n\n") !== false)
						{
							$out = str_replace("\n\n", "\n", $out);
						}
						$tmp = md5($cachename);
						$hash_dir = $this->cache_dir . '/' . substr($tmp, 0, 2).'/'.substr($tmp, 2, 2);
						if (!is_dir($hash_dir))
						{
							mkdir($hash_dir, 0777, true);
						}
						if (file_put_contents($hash_dir . '/' . $tmp . '.php', '<?php exit;?>' . $data . $out, LOCK_EX) === false)
						{
							trigger_error('can\'t write:' . $hash_dir . '/' . $tmp . '.php');
						}
						$this->template = array();
					}
				}
			}
		}

		$this->_seterror--;
		if (!$this->_seterror)
		{
			error_reporting($this->_errorlevel);
		}

		return $out; // 返回html数据
	}

	/**
	 * 编译模板函数
	 *
	 * @access  public
	 * @param   string	  $filename
	 *
	 * @return  sring		编译后文件地址
	 */
	function make_compiled($filename)
	{
		$name = $this->get_compiled_name($filename);
		$dir = dirname($name);
		!is_dir($dir) && mkdir($dir, 0777, TRUE);
		if ($this->_expires)
		{
			$expires = $this->_expires - $this->cache_lifetime;
		}
		else
		{
			$filestat = @stat($name);
			$expires  = $filestat['mtime'];
		}

		$filestat = @stat($filename);

		if ($filestat['mtime'] <= $expires && !$this->force_compile)
		{
			if (file_exists($name))
			{
				$source = $this->_require($name);
				if ($source == '')
				{
					$expires = 0;
				}
			}
			else
			{
				$source = '';
				$expires = 0;
			}
		}

		if ($this->force_compile || $filestat['mtime'] > $expires)
		{
			$this->_current_file = $filename;
			$source = $this->fetch_str(file_get_contents($filename));

			if (file_put_contents($name, $source, LOCK_EX) === false)
			{
				trigger_error('can\'t write:' . $name);
			}

			$source = $this->_eval($source);
		}

		return $source;
	}

	/**
	 * 处理字符串函数
	 *
	 * @access  public
	 * @param   string	 $source
	 *
	 * @return  sring
	 */
	private function _callabck_select($matches)
	{
		return $this->select($matches[1]);
	}
	function fetch_str($source)
	{
		if (!defined('MYADMIN')){
			$source = $this->smarty_prefilter_preCompile($source);
		}
		return preg_replace_callback("/{([^\}\{\n]*)}/", "self::_callabck_select", $source);
		//return preg_replace("/{([^\}\{\n]*)}/e", "\$this->select('\\1');", $source);
	}

	/**
	 * 判断是否缓存
	 *
	 * @access  public
	 * @param   string	 $filename
	 * @param   sting	  $cache_id
	 *
	 * @return  bool
	 */
	function is_cached($filename, $cache_id = '')
	{
		$template_dir = $this->template_dir;
		if ($filename{0} == '@' || $filename{0} == '/')
		{
			$filename = substr($filename, 1);
			$template_dir = $this->base_template_dir;
		}
		$filename = $template_dir . '/' . $filename;
		$cachename =  $this->get_cache_name($filename) . '_' . $cache_id;
		if ($this->caching == true && $this->direct_output == false)
		{
			$tmp = md5($cachename);
			$hash_dir = $this->cache_dir . '/' . substr($tmp, 0, 2).'/'.substr($tmp, 2, 2);
			if ($data = @file_get_contents($hash_dir . '/' . $tmp . '.php'))
			{
				$data = substr($data, 13);
				$pos  = strpos($data, '<');
				$paradata = substr($data, 0, $pos);
				$para	 = @unserialize($paradata);
				if ($para === false || $this->_nowtime > $para['expires'])
				{
					$this->caching = false;

					return false;
				}
				$this->_expires = $para['expires'];

				$this->template_out = substr($data, $pos);

				foreach ($para['template'] AS $val)
				{
					$stat = @stat($val);
					if ($para['maketime'] < $stat['mtime'])
					{
						$this->caching = false;

						return false;
					}
				}
			}
			else
			{
				$this->caching = false;

				return false;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 处理{}标签
	 *
	 * @access  public
	 * @param   string	  $tag
	 *
	 * @return  sring
	 */
	function select($tag)
	{
		$tag = stripslashes(trim($tag));

		if (empty($tag))
		{
			return '{}';
		}
		elseif ($tag{0} == '*' && substr($tag, -1) == '*') // 注释部分
		{
			return '';
		}
		elseif ($tag{0} == '$') // 变量
		{
			return '<?php echo ' . $this->get_val(substr($tag, 1)) . '; ?>';
		}
		elseif ($tag{0} == '/') // 结束 tag
		{
			switch (substr($tag, 1))
			{
				case 'if':
					return '<?php endif; ?>';
					break;

				case 'foreach':
					if ($this->_foreachmark == 'foreachelse')
					{
						$output = '<?php endif; unset($_from); ?>';
					}
					else
					{
						array_pop($this->_patchstack);
						$output = '<?php endforeach; endif; unset($_from); ?>';
					}
					$output .= "<?php \$this->pop_vars();; ?>";

					return $output;
					break;

				case 'literal':
					return '';
					break;

				default:
					return '{'. $tag .'}';
					break;
			}
		}
		else
		{
			$tag_sel = array_shift(explode(' ', $tag));
			switch ($tag_sel)
			{
				case 'if':

					return $this->_compile_if_tag(substr($tag, 3));
					break;

				case 'else':

					return '<?php else: ?>';
					break;

				case 'elseif':

					return $this->_compile_if_tag(substr($tag, 7), true);
					break;

				case 'foreachelse':
					$this->_foreachmark = 'foreachelse';

					return '<?php endforeach; else: ?>';
					break;

				case 'foreach':
					$this->_foreachmark = 'foreach';
					if(!isset($this->_patchstack))
					{
						$this->_patchstack = array();
					}
					return $this->_compile_foreach_start(substr($tag, 8));
					break;

				case 'assign':
					$t = $this->get_para(substr($tag, 7),0);

					if ($t['value']{0} == '$')
					{
						/* 如果传进来的值是变量，就不用用引号 */
						$tmp = '$this->assign(\'' . $t['var'] . '\',' . $t['value'] . ');';
					}
					else
					{
						$tmp = '$this->assign(\'' . $t['var'] . '\',\'' . addcslashes($t['value'], "'") . '\');';
					}
					// $tmp = $this->assign($t['var'], $t['value']);

					return '<?php ' . $tmp . ' ?>';
					break;

				case 'include':
					$t = $this->get_para(substr($tag, 8), 0);

					return '<?php echo $this->fetch(' . "'$t[file]'" . '); ?>';
					break;
				case 'page':
					$t = $this->get_para(substr($tag, 5), 0);
					return '<?php echo $this->page(' . "'$t[file]'" . '); ?>';
					break;

				case 'insert_scripts':
					$t = $this->get_para(substr($tag, 15), 0);

					return '<?php echo $this->smarty_insert_scripts(' . $this->make_array($t) . '); ?>';
					break;

				case 'insert_jqplug':
					$t = $this->get_para(substr($tag, 14), 0);
					return '<?php echo $this->smarty_insert_jqplug(' . $this->make_array($t) . '); ?>';
					break;

				case 'insert_styles':

					$t = $this->get_para(substr($tag, 11), 0);
					return '<?php echo $this->smarty_insert_styles(' . $this->make_array($t) . '); ?>';
					break;

				case 'create_pages':
					$t = $this->get_para(substr($tag, 13), 0);

					return '<?php echo $this->smarty_create_pages(' . $this->make_array($t) . '); ?>';
					break;

				case 'insert' :
					$t = $this->get_para(substr($tag, 7), false);

					//$out = "<?php \n" . '$k = ' . preg_replace("/(\'\\$[^,]+)/e" , "stripslashes(trim('\\1','\''));", var_export($t, true)) . ";\n";
					$out = "<?php \n" . '$k = ' . preg_replace_callback("/(\'\\$[^,]+)/" , function($matches){ return stripslashes(trim($matches[1],'\'')); }, var_export($t, true)) . ";\n";
					$out .= 'echo \'<!----|\'.$this->_echash . $k[\'name\'] . \'|\' . serialize($k) . $this->_echash . \'|---->\';' . "\n?>";

					return $out;
					break;

				case 'literal':
					return '';
					break;

				case 'cycle' :
					$t = $this->get_para(substr($tag, 6), 0);

					return '<?php echo $this->cycle(' . $this->make_array($t) . '); ?>';
					break;

				case 'html_options':
					$t = $this->get_para(substr($tag, 13), 0);

					return '<?php echo $this->html_options(' . $this->make_array($t) . '); ?>';
					break;

				case 'html_select_date':
					$t = $this->get_para(substr($tag, 17), 0);

					return '<?php echo $this->html_select_date(' . $this->make_array($t) . '); ?>';
					break;

				case 'html_radios':
					$t = $this->get_para(substr($tag, 12), 0);

					return '<?php echo $this->html_radios(' . $this->make_array($t) . '); ?>';
					break;

				case 'html_checkboxs':
					$t = $this->get_para(substr($tag, 12), 0);

					return '<?php echo $this->html_checkboxs(' . $this->make_array($t) . '); ?>';
					break;

				case 'html_select_time':
					$t = $this->get_para(substr($tag, 12), 0);
					return '<?php echo $this->html_select_time(' . $this->make_array($t) . '); ?>';
					break;
			   case 'Hook':
					 $t = $this->get_para(substr($tag, 4), 0);
					 return '<?php hook_api(' . $this->make_array($t) . '); ?>';
					 break;
				default:
					return '{' . $tag . '}';
					break;
			}
		}
	}

	/**
	 * 处理smarty标签中的变量标签
	 *
	 * @access  public
	 * @param   string	 $val
	 *
	 * @return  bool
	 */
	function get_val($val)
	{

		if (strrpos($val, '[') !== false)
		{
			// $val = preg_replace("/\[([^\[\]]*)\]/eis", "'.'.str_replace('$','\$','\\1')", $val);
			$val = preg_replace_callback("/\[([^\[\]]*)\]/is", function($matches){return '.'.$matches[1];}, $val);
		}

		if (strrpos($val, '|') !== false)
		{
			$moddb = explode('|', $val);
			$val = array_shift($moddb);
		}

		if (empty($val))
		{
			return '';
		}

		if (strpos($val, '.$') !== false)
		{
			$all = explode('.$', $val);

			foreach ($all AS $key => $val)
			{
				$all[$key] = $key == 0 ? $this->make_var($val) : '['. $this->make_var($val) . ']';
			}
			$p = implode('', $all);
		}
		else
		{
			$p = $this->make_var($val);
		}

		if (!empty($moddb))
		{
			foreach ($moddb AS $key => $mod)
			{
				$s = explode(':', $mod,2);
                if ($s[1]) {
                    $p = $s[0].'('.$p.','.$s[1].')';
                } else {
                    $p = $s[0].'('.$p.')';
                }
			}
		}

		return $p;
	}

	/**
	 * 处理去掉$的字符串
	 *
	 * @access  public
	 * @param   string	 $val
	 *
	 * @return  bool
	 */
	function make_var($val)
	{
		if (strrpos($val, '.') === false)
		{
			if (isset($this->_var[$val]) && isset($this->_patchstack[$val]))
			{
				$val = $this->_patchstack[$val];
			}
			$p = '$this->_var[\'' . $val . '\']';
		}
		else
		{
			$t = explode('.', $val);
			$_var_name = array_shift($t);
			if (isset($this->_var[$_var_name]) && isset($this->_patchstack[$_var_name]))
			{
				$_var_name = $this->_patchstack[$_var_name];
			}
			if ($_var_name == 'smarty')
			{
				 $p = $this->_compile_smarty_ref($t);
			}
			else
			{
				$p = '$this->_var[\'' . $_var_name . '\']';
			}
			foreach ($t AS $val)
			{
				$p.= '[\'' . $val . '\']';
			}
		}

		return $p;
	}

	/**
	 * 处理insert外部函数/需要include运行的函数的调用数据
	 *
	 * @access  public
	 * @param   string	 $val
	 * @param   int		 $type
	 *
	 * @return  array
	 */
	function get_para($val, $type = 1) // 处理insert外部函数/需要include运行的函数的调用数据
	{
		$pa = $this->str_trim($val);
		foreach ($pa AS $value)
		{
			if (strrpos($value, '='))
			{
				list($a, $b) = explode('=', str_replace(array(' ', '"', "'", '&quot;'), '', $value));
				if ($b{0} == '$')
				{
					if ($type)
					{
						eval('$para[\'' . $a . '\']=' . $this->get_val(substr($b, 1)) . ';');
					}
					else
					{
						$para[$a] = $this->get_val(substr($b, 1));
					}
				}
				else
				{
					$para[$a] = $b;
				}
			}
		}

		return $para;
	}

	/**
	 * 判断变量是否被注册并返回值
	 *
	 * @access  public
	 * @param   string	 $name
	 *
	 * @return  mix
	 */
	function &get_template_vars($name = null)
	{
		if (empty($name))
		{
			return $this->_var;
		}
		elseif (!empty($this->_var[$name]))
		{
			return $this->_var[$name];
		}
		else
		{
			$_tmp = null;

			return $_tmp;
		}
	}

	/**
	 * 处理if标签
	 *
	 * @access  public
	 * @param   string	 $tag_args
	 * @param   bool	   $elseif
	 *
	 * @return  string
	 */
	function _compile_if_tag($tag_args, $elseif = false)
	{
		preg_match_all('/\-?\d+[\.\d]+|\'[^\'|\s]*\'|"[^"|\s]*"|[\$\w\.]+|!==|===|==|!=|<>|<<|>>|<=|>=|&&|\|\||\(|\)|,|\!|\^|=|&|<|>|~|\||\%|\+|\-|\/|\*|\@|\S/', $tag_args, $match);

		$tokens = $match[0];
		// make sure we have balanced parenthesis
		$token_count = array_count_values($tokens);
		if (!empty($token_count['(']) && $token_count['('] != $token_count[')'])
		{
			// $this->_syntax_error('unbalanced parenthesis in if statement', E_USER_ERROR, __FILE__, __LINE__);
		}

		for ($i = 0, $count = count($tokens); $i < $count; $i++)
		{
			$token = &$tokens[$i];
			switch (strtolower($token))
			{
				case 'eq':
					$token = '==';
					break;

				case 'ne':
				case 'neq':
					$token = '!=';
					break;

				case 'lt':
					$token = '<';
					break;

				case 'le':
				case 'lte':
					$token = '<=';
					break;

				case 'gt':
					$token = '>';
					break;

				case 'ge':
				case 'gte':
					$token = '>=';
					break;

				case 'and':
					$token = '&&';
					break;

				case 'or':
					$token = '||';
					break;

				case 'not':
					$token = '!';
					break;

				case 'mod':
					$token = '%';
					break;

				default:
					if ($token[0] == '$')
					{
						$token = $this->get_val(substr($token, 1));
					}
					break;
			}
		}

		if ($elseif)
		{
			return '<?php elseif (' . implode(' ', $tokens) . '): ?>';
		}
		else
		{
			return '<?php if (' . implode(' ', $tokens) . '): ?>';
		}
	}

	/**
	 * 处理foreach标签
	 *
	 * @access  public
	 * @param   string	 $tag_args
	 *
	 * @return  string
	 */
	function _compile_foreach_start($tag_args)
	{
		$attrs = $this->get_para($tag_args, 0);
		$arg_list = array();
		$from = $attrs['from'];
		if(isset($this->_var[$attrs['item']]) && !isset($this->_patchstack[$attrs['item']]))
		{
			$this->_patchstack[$attrs['item']] = $attrs['item'] . '_' . str_replace(array(' ', '.'), '_', microtime());
			$attrs['item'] = $this->_patchstack[$attrs['item']];
		}
		else
		{
			$this->_patchstack[$attrs['item']] = $attrs['item'];
		}
		$item = $this->get_val($attrs['item']);

		if (!empty($attrs['key']))
		{
			$key = $attrs['key'];
			$key_part = $this->get_val($key).' => ';
		}
		else
		{
			$key = null;
			$key_part = '';
		}

		if (!empty($attrs['name']))
		{
			$name = $attrs['name'];
		}
		else
		{
			$name = null;
		}

		$output = '<?php ';
		$output .= "\$_from = $from; if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); }; \$this->push_vars('$attrs[key]', '$attrs[item]');";

		if (!empty($name))
		{
			$foreach_props = "\$this->_foreach['$name']";
			$output .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
			$output .= "if ({$foreach_props}['total'] > 0):\n";
			$output .= "	foreach (\$_from AS $key_part$item):\n";
			$output .= "		{$foreach_props}['iteration']++;\n";
		}
		else
		{
			$output .= "if (count(\$_from)):\n";
			$output .= "	foreach (\$_from AS $key_part$item):\n";
		}
		return $output . '?>';
	}

	/**
	 * 将 foreach 的 key, item 放入临时数组
	 *
	 * @param  mixed	$key
	 * @param  mixed	$val
	 *
	 * @return  void
	 */
	function push_vars($key, $val)
	{
		if (!empty($key))
		{
			array_push($this->_temp_key, "\$this->_vars['$key']='" .$this->_vars[$key] . "';");
		}
		if (!empty($val))
		{
			array_push($this->_temp_val, "\$this->_vars['$val']='" .$this->_vars[$val] ."';");
		}
	}

	/**
	 * 弹出临时数组的最后一个
	 *
	 * @return  void
	 */
	function pop_vars()
	{
		$key = array_pop($this->_temp_key);
		$val = array_pop($this->_temp_val);

		if (!empty($key))
		{
			eval($key);
		}
	}

	/**
	 * 处理smarty开头的预定义变量
	 *
	 * @access  public
	 * @param   array   $indexes
	 *
	 * @return  string
	 */
	function _compile_smarty_ref(&$indexes)
	{
		/* Extract the reference name. */
		$_ref = $indexes[0];
		switch ($_ref)
		{

			case 'define':
				$compiled_ref = $indexes[1];
				$indexes = array();
				return $compiled_ref;
				break;

			case 'now':
				$compiled_ref = 'time()';
				break;

			case 'foreach':
				array_shift($indexes);
				$_var = $indexes[0];
				$_propname = $indexes[1];
				switch ($_propname)
				{
					case 'index':
						array_shift($indexes);
						$compiled_ref = "(\$this->_foreach['$_var']['iteration'] - 1)";
						break;

					case 'first':
						array_shift($indexes);
						$compiled_ref = "(\$this->_foreach['$_var']['iteration'] <= 1)";
						break;

					case 'last':
						array_shift($indexes);
						$compiled_ref = "(\$this->_foreach['$_var']['iteration'] == \$this->_foreach['$_var']['total'])";
						break;

					case 'show':
						array_shift($indexes);
						$compiled_ref = "(\$this->_foreach['$_var']['total'] > 0)";
						break;

					default:
						$compiled_ref = "\$this->_foreach['$_var']";
						break;
				}
				break;

			case 'get':
				$compiled_ref = '$_GET';
				break;

			case 'post':
				$compiled_ref = '$_POST';
				break;

			case 'cookies':
				$compiled_ref = '$_COOKIE';
				break;

			case 'env':
				$compiled_ref = '$_ENV';
				break;

			case 'server':
				$compiled_ref = '$_SERVER';
				break;

			case 'request':
				$compiled_ref = '$_REQUEST';
				break;

			case 'session':
				$compiled_ref = '$_SESSION';
				break;

			default:
				// $this->_syntax_error('$smarty.' . $_ref . ' is an unknown reference', E_USER_ERROR, __FILE__, __LINE__);
				break;
		}
		array_shift($indexes);

		return $compiled_ref;
	}

	/**
	 * 生成script脚本
	 *
	 * @param array $args
	 * @return string
	 */
	function smarty_insert_scripts($args)
	{
		static $scripts = array();
		$js_path = $this->js_path;
		$arr = explode(',', str_replace(' ', '', $args['files']));
		if (isset($args['root']))
		{
			switch($args['root'])
			{
				case 'assets':
					$js_path = $this->assets_url.'lib/';
					break;
				case 'assets_root':
					$js_path=$this->assets_url;
					break;
				case 'assets_other':
					$js_path = $this->assets_url.'other/';
					break;
			}
		}
		$str = '';
		foreach ($arr AS $val)
		{
			if (in_array($val, $scripts) == false)
			{
				$scripts[] = $val;
				if ($val{0} == '.')
				{
					$val .= '?'.$this->js_ver;
					$str .= '<script type="text/javascript" src="' . $val . '"></script>' . "\r\n";
				}
				else
				{

					if (isset($this->libs[$val]))
					{
						$src = $this->libs[$val];
					}
					else
					{
						$src = $js_path . $val .'?'.$this->js_ver;
					}
					$str .= '<script type="text/javascript" src="'. $src . '"></script>' . "\r\n";
				}
			}
		}

		return $str;
	}
	/**
	 * 生成script插件脚本
	 *
	 * @param array $args
	 * @return string
	 */
	function smarty_insert_jqplug($args)
	{
		static $scripts = array();
		$arr = explode(',', str_replace(' ', '', $args['files']));
		foreach ($arr AS $val)
		{
			if (in_array($val, $scripts) == false)
			{
				$scripts[] = $val;
				$js = $this->js_path.  $val . '/jquery.'.$val. '.js';
				$css = $this->js_path. $val . '/jquery.'.$val. '.css';
				#if (file_exists($this->base_path.$js))
				$str .= '<script type="text/javascript" src="'.$js.'"></script>' . "\r\n";
				#if (file_exists($this->base_path.$css))
				$str .= '<link rel="stylesheet" type="text/css" href="' . $css . '" />' . "\r\n";

			}
		}

		return $str;
	}

	/**
	 * 生成样式
	 *
	 * @param array $args
	 * @return string
	 */
	function smarty_insert_styles($args){
		static $csss = array();
		$css_path = $this->css_path;
		$arr = explode(',', str_replace(' ', '', $args['files']));
		if (isset($args['root']))
		{
			switch($args['root'])
			{
				case 'assets':
					$css_path = $this->assets_url.'css/';
					break;
			}
		}
		$str = '';
		foreach ($arr AS $val)
		{
			if (in_array($val, $csss) == false)
			{
				$csss[] = $val;
				if ($val{0} == '.')
				{
					//..
				}
				else
				{
					$val = $css_path . $val.'?'.$this->css_ver;
				}
				$str .= '<link href="' . $val . '" rel="stylesheet" type="text/css" />' . "\r\n";
			}
		}

		return $str;
	}
	function stripvtag($str){
		return str_replace("\\\"", '"', $str);
	}
	function smarty_prefilter_preCompile($source)
	{
		$file_type = strtolower(strrchr($this->_current_file, '.'));

		/* 替换文件编码头部 */
		if (strpos($source, "\xEF\xBB\xBF") !== FALSE)
		{
			$source = str_replace("\xEF\xBB\xBF", '', $source);
		}

		$pattern = array(
			'/<!--[^>|\n]*?({.+?})[^<|{|\n]*?-->/', // 替换smarty注释
			'/<!--[^<|>|{|\n]*?-->/',			   // 替换不换行的html注释
			'/(href=["|\'])\.\.\/(.*?)(["|\'])/i',  // 替换相对链接
			'/((?:background|src)\s*=\s*["|\'])(?:\.\/|\.\.\/)?(images\/.*?["|\'])/is', // 在images前加上 $tmp_dir
			'/((?:background|background-image):\s*?url\()(?:\.\/|\.\.\/)?(images\/)/is', // 在images前加上 $tmp_dir
			'/([\'|"])\.\.\//is', // 以../开头的路径全部修正为空
			);
		$replace = array(
			'\1',
			'',
			'\1\2\3',
			'\1' . $this->img_path . '\2',
			'\1' . $this->img_path . '\2',
			'\1',
			);
		$source = preg_replace($pattern, $replace, $source);
		$source = preg_replace_callback('/\{eval (.*?)\}/is', 'self::_callback_stripvtag', $source);
		return $source;
	}
	private function _callback_stripvtag($matches)
	{
		return $this->stripvtag('<?php '.$matches[1].' ?>');
	}

	function insert_mod($name) // 处理动态内容
	{
		list($fun, $para) = explode('|', $name);
		$para = unserialize($para);
		$fun = 'insert_' . $fun;
		return $fun($para);
	}

	function str_trim($str)
	{
		/* 处理'a=b c=d k = f '类字符串，返回数组 */
		while (strpos($str, '= ') != 0)
		{
			$str = str_replace('= ', '=', $str);
		}
		while (strpos($str, ' =') != 0)
		{
			$str = str_replace(' =', '=', $str);
		}

		return explode(' ', trim($str));
	}

	function _eval($content)
	{
		ob_start();
		eval('?' . '>' . trim($content));
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function _require($filename)
	{
		ob_start();
		include $filename;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function html_options($arr)
	{
		$selected = $arr['selected'];

		if ($arr['options'])
		{
			$options = (array)$arr['options'];
		}
		elseif ($arr['output'])
		{
			if ($arr['values'])
			{
				foreach ($arr['output'] AS $key => $val)
				{
					$options["{$arr[values][$key]}"] = $val;
				}
			}
			else
			{
				$options = array_values((array)$arr['output']);
			}
		}
		if ($options)
		{
			foreach ($options AS $key => $val)
			{
				$out .= $key == $selected ? "<option value=\"$key\" selected>$val</option>" : "<option value=\"$key\">$val</option>";
			}
		}

		return $out;
	}

	function html_select_date($arr)
	{
		$pre = $arr['prefix'];
		if (isset($arr['time']))
		{
			if (intval($arr['time']) > 10000)
			{
				$arr['time'] = gmdate('Y-m-d', $arr['time'] + 8*3600);
			}
			$t	 = explode('-', $arr['time']);
			$year  = strval($t[0]);
			$month = strval($t[1]);
			$day   = strval($t[2]);
		}
		$now = gmdate('Y', $this->_nowtime);
		if (isset($arr['start_year']))
		{
			if (abs($arr['start_year']) == $arr['start_year'])
			{
				$startyear = $arr['start_year'];
			}
			else
			{
				$startyear = $arr['start_year'] + $now;
			}
		}
		else
		{
			$startyear = $now - 3;
		}

		if (isset($arr['end_year']))
		{
			if (strlen(abs($arr['end_year'])) == strlen($arr['end_year']))
			{
				$endyear = $arr['end_year'];
			}
			else
			{
				$endyear = $arr['end_year'] + $now;
			}
		}
		else
		{
			$endyear = $now + 3;
		}

		$out = "<select name=\"{$pre}Year\">";
		for ($i = $startyear; $i <= $endyear; $i++)
		{
			$out .= $i == $year ? "<option value=\"$i\" selected>$i</option>" : "<option value=\"$i\">$i</option>";
		}
		if ($arr['display_months'] != 'false')
		{
			$out .= "</select>&nbsp;<select name=\"{$pre}Month\">";
			for ($i = 1; $i <= 12; $i++)
			{
				$out .= $i == $month ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
			}
		}
		if ($arr['display_days'] != 'false')
		{
			$out .= "</select>&nbsp;<select name=\"{$pre}Day\">";
			for ($i = 1; $i <= 31; $i++)
			{
				$out .= $i == $day ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
			}
		}

		return $out . '</select>';
	}

	function html_radios($arr)
	{
		$name	= $arr['name'];
		$checked = $arr['checked'];
		$options = $arr['options'];

		$out = '';
		foreach ($options AS $key => $val)
		{
			$out .= $key == $checked ? "<input type=\"radio\" name=\"$name\" value=\"$key\" checked>&nbsp;{$val}&nbsp;"
				: "<input type=\"radio\" name=\"$name\" value=\"$key\">&nbsp;{$val}&nbsp;";
		}

		return $out;
	}

	function html_checkboxs($arr) {
		$name	= $arr['name'];
		$checked = isset($arr['checked']) ? $arr['checked'] : array();
		$options = $arr['options'];

		$out = '';
		foreach ($options AS $key => $val)
		{
			if ($val == "<br />") {
				$out .= "<br />";
				continue;
			}
			if ($checked['0'] == 'ALL') {
				$out .= "<input type=\"checkbox\" name=\"$name\" value=\"$key\" checked>&nbsp;{$val['name']}&nbsp;";
			}
			else {
				$out .= in_array($key, $checked) ? "<input type=\"checkbox\" name=\"$name\" value=\"$key\" checked>&nbsp;{$val['name']}&nbsp;"
					: "<input type=\"checkbox\" name=\"$name\" value=\"$key\">&nbsp;{$val['name']}&nbsp;";
			}
		}

		return $out;
	}

	function html_select_time($arr)
	{
		$pre = $arr['prefix'];
		if (isset($arr['time']))
		{
			$arr['time'] = gmdate('H-i-s', $arr['time'] + 8*3600);
			$t	 = explode('-', $arr['time']);
			$hour  = strval($t[0]);
			$minute = strval($t[1]);
			$second   = strval($t[2]);
		}
		$out = '';
		if ($arr['display_hours'] != 'false')
		{
			$out .= "<select name=\"{$pre}Hour\">";
			for ($i = 0; $i <= 23; $i++)
			{
				$out .= $i == $hour ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
			}

			$out .= "</select>&nbsp;";
		}
		if ($arr['display_minutes'] != 'false')
		{
			$out .= "<select name=\"{$pre}Minute\">";
			for ($i = 0; $i <= 59; $i++)
			{
				$out .= $i == $minute ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
			}

			$out .= "</select>&nbsp;";
		}
		if ($arr['display_seconds'] != 'false')
		{
			$out .= "<select name=\"{$pre}Second\">";
			for ($i = 0; $i <= 59; $i++)
			{
				$out .= $i == $second ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">$i</option>";
			}

			$out .= "</select>&nbsp;";
		}

		return $out;
	}
	function cycle($arr)
	{
		static $k, $old;

		$value = explode(',', $arr['values']);
		if ($old != $value)
		{
			$old = $value;
			$k = 0;
		}
		else
		{
			$k++;
			if (!isset($old[$k]))
			{
				$k = 0;
			}
		}

		echo $old[$k];
	}

	function make_array($arr)
	{
		$out = '';
		foreach ($arr AS $key => $val)
		{
			if ($val{0} == '$')
			{
				$out .= $out ? ",'$key'=>$val" : "array('$key'=>$val";
			}
			else
			{
				$out .= $out ? ",'$key'=>'$val'" : "array('$key'=>'$val'";
			}
		}

		return $out . ')';
	}

	function smarty_create_pages($params)
	{
		extract($params);

		if (empty($page))
		{
			$page = 1;
		}

		if (!empty($count))
		{
			$str = "<option value='1'>1</option>";
			$min = min($count - 1, $page + 3);
			for ($i = $page - 3 ; $i <= $min ; $i++)
			{
				if ($i < 2)
				{
					continue;
				}
				$str .= "<option value='$i'";
				$str .= $page == $i ? " selected='true'" : '';
				$str .= ">$i</option>";
			}
			if ($count > 1)
			{
				$str .= "<option value='$count'";
				$str .= $page == $count ? " selected='true'" : '';
				$str .= ">$count</option>";
			}
		}
		else
		{
			$str = '';
		}

		return $str;
	}

}

?>
