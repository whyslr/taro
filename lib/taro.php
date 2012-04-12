<?php
/**
 * 项目入口文件
 *
 * @category core
 * @package lib
 * @author whyslr
 */
final class taro {	
	/**
	 * 当前项目运行设置参数
	 */
	static private $_config = null;
	
	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct () {
	}
	
	/**
	 * 静态构造函数
	 *
	 * @return taro
	 */
	static public function __taro () {
		return new taro ();
	}
		
	/**
	 * 加载框架核心文件
	 *
	 * @return void
	 */
	static public function initialize () {
		$include_path = array ();//explode (PS, get_include_path () );
		$include_path[] = SYS_ROOT . 'lib';
		$include_path[] = APP_ROOT;
		set_include_path (join (PS, $include_path ) );
		
		include (join (DS, array ('core', 'taro', 'autoload.php' ) ) );
		core_taro_autoload::register ();
		self::__initialize ();
	}
	
	/**
	 * 初始化项目目录结构
	 *
	 * @return void
	 */
	static private function __initialize () {		
		$_initialize_error = array ();		
		$include_path = explode (PS, get_include_path () );
		$_config = self::__config ();
		$_config['app_dirs'][] = 'lib';
		$_config['app_dirs'][] = $_config['app_tpl_dir'];
		$_config['app_dirs'][] = $_config['app_cache_dir'];
		$_config['app_dirs'][] = $_config['app_cache_dir'] . DS . 'tpl';

		$_initialize['dir'] = array($_config['app_root'] );
		$_initialize['file'] = array ('index.php' );
		foreach ($_config['app_dirs'] as $v ) {
			$_initialize['dir'][] = $_config['app_root'] . DS . $v;
			
			if ($v == 'lib' ) {
				$include_path[] = APP_ROOT . $_config['app_root'] . DS . $v;
			}
		}
		
		foreach ($_initialize['dir'] as $v) {
			$v = APP_ROOT . $v;
			if (!file_exists ($v ) ) {
				if (!mkdir ($v, 0777 ) ) {
					$_initialize_error = 'mkdir ' . $v . ' occur error.';
				}
			}
		}
		
		if (count ($_initialize_error ) ) {
			exit (join ('<br /', $_initialize_error ) );
		}					
		set_include_path (join (PS, $include_path ) );
				
		$taro_file = array ($_config['app']);
		if (!empty ($_config['block'] ) ) {
			$taro_file[] = $_config['block'];
		}
		$taro_file[] = 'index.php';
		$taro_file = APP_ROOT . join (DS, $taro_file );
		if(!file_exists ($taro_file ) ) {
			$fp = fopen ($taro_file, 'w+b' );
			fwrite ($fp, 'simple test in taro' );
			fclose ($fp );
			unset ($fp );
		}
		
		$taro_file = array ($_config['app']);
		if (!empty ($_config['block'] ) ) {
			$taro_file[] = $_config['block'];
		}
		$taro_file[] = $_config['file'] . '.php';
		
		if (is_array ($_config['param'] ) ) {
			$_GET = array_merge ($_GET, $_config['param'] );
			$_REQUEST = array_merge ($_REQUEST, $_config['param'] );
		}
		unset ($_initialize_error, $_config, $_initialize, $include_path );

		if (!@include (join (DS, $taro_file ) ) ) {
			$taro_file[count($taro_file) - 1 ] = 'index.php';
			include ( join (DS, $taro_file )  );
		}
	}
	
	/**
	 * 获取项目运行设置参数
	 *
	 * @return array
	 */
	static public function __config () {
		$returnValue = self::$_config;
		
		if (empty ($returnValue ) ) {
			$returnValue = unserialize (SYS_APP );
			$_app_keys = array_keys ($returnValue );
			
			$url = core_taro_url::url_arr ( );
			foreach ($returnValue as $k => $v ) {
				if (!empty ($v['app_def'] ) ) {
					$url['app_def_app'] = $k;
					break;
				}
				
				if (empty ($url['app_def_app'] ) ) {
					$url['app_def_app'] = $k;
				}				
			}
			if (!is_array ($returnValue[ $url['app'] ] ) ) {
				$url['app'] = $_app_keys[0];
			}
			$returnValue = $returnValue[ $url['app'] ];
			
			//if (empty ($url['app'] ) ) {
				$_app = self::__app ($returnValue);
			//} else {				
			//	$_app = self::__app (array ($url['app'] => $returnValue ) );
			//}
			
			if (!in_array ($url['app'], $_app_keys) ) {
				$url['app'] = $_app['app'];
			}
			
			if (!in_array ($url['block'], $_app['app_dirs'] ) ) {
				$url['block'] = $_app['app_def_block'];
			}
			
			if (!empty ($_app['app_suffix'] ) && preg_match ('~[0-9a-z]+(?=' . $_app['app_suffix'] . ')~i', $url['file'] ) ) {
				$url['file'] = substr ($url['file'], 0, strlen($url['file']) - strlen ($_app['app_suffix'] ) );
			}				
			
			if (!empty ($url['param'] ) ) {
				$param = array_keys ($url['param'] );
				$param = $param[count ($param ) - 1 ];
				
				if (!empty ($_app['app_suffix'] ) && preg_match ('~[0-9a-z]+(?=' . $_app['app_suffix'] . ')~i', $url['param'][ $param ] ) ) {
					$url['param'][ $param ] = substr ($url['param'][ $param ], 0, strlen($url['param'][ $param ]) - strlen ($_app['app_suffix'] ) );
				}
			}
	
			$returnValue = array_merge ($_app, $url );
			self::$_config = $returnValue;
		}

		if (!is_array ($returnValue )) {
			throw new Exception ('taro __config occurs error.' );
		}

		return $returnValue;
	}
	
	/**
	* 获取当前访问的APP运行设置参数
	*
	* @data array
	* @return array
	*/
	static private function __app ($data) {
		$returnValue = $data;
		
		$_app = array (
			'app_root'			=>	'',
			'app_dirs'			=>	'',
			'app_suffix'		=>	'',
			'app_def'			=>	false,
			'app_tpl_dir'		=>	'template',
			'app_cache_dir'		=>	'cache',
		);		
		if (!is_array ($returnValue ) ) {			
			$returnValue = array ();
		}
		
		foreach ($_app as $k => $v ) {
			if (empty ($returnValue[ $k ] ) ) {
				$returnValue[ $k ] = $v;
			}
		}
		
		$returnValue['app_dirs'] = explode (',', $returnValue['app_dirs'] );
		foreach ($returnValue['app_dirs'] as $k => $v ) {
			$v = preg_split ('~[^0-9a-z]~i', $v );
			$returnValue['app_dirs'][$k] = $v[0];
		}
		$returnValue['app_def_block'] = $returnValue['app_dirs'][0];
		$returnValue['app_def_file'] = 'index';
		
		return $returnValue;		
	}
	
	/**
	 * 模版操作类接口 
	 *
	 * @return core_taro_tpl
	 */
	static public function tpl () {
		return new core_taro_tpl ();
	}
	
	/**
	 * 将数组转换成taro可识别的URL
	 *
	 * @data = array ('block' => string, 'file' => string, array())
	 * @return string
	 */
	static public function url ($data = array ()) {
		return core_taro_url::arr_url ($data );
	} 
}

taro::initialize ();
?>