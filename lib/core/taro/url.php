<?php
/**
 * URL解析类
 *
 * @category core
 * @package taro
 * @author whyslr
 */
class core_taro_url {
	/**
	* URL参数连接符
	*/
	static private $_url_param_connector = '-';
	
	/**
	 * URL目录连接符
	 */
	static private $_url_dir_connector = '/';
	
	/**
	 * 构造函数
	 */
	public function __construct () {
	}

	/**
	 * 构造函数
	 */
	private function core_taro_url () {
	}

	/**
	 * 解释URL
	 *
	 * @return array
	 */
	 static public function url_arr () {	 	
		$returnValue = array (
			'app'		=>	null,
			'block'		=>	null,
			'file'		=>	'index',
			'param'		=>	null,
		);
		
	 	$url = array (
	 		'PATH_INFO'			=> null,
	 		'REQUEST_URI'		=> null,
	 		'PHP_SELF'			=> null,
	 	);
	 	
	 	foreach (array_keys ($url ) as $v ) {
	 		if (strlen ($_SERVER[ $v ] ) ) {
	 			$url = $_SERVER[ $v ];
	 			break;
	 		}
	 	}
	 	
	 	if (preg_match ('/index\.php/i', $url ) ) {
		 	$url = preg_split ('/index\.php/i', $url );
		 	$url = $url[1];
		}
		
		$_len = 1;
		if (preg_match ('~^' . dirname ($_SERVER['SCRIPT_NAME'] ) . '~i', $url ) ) {
			$_len = strlen (dirname ($_SERVER['SCRIPT_NAME'] ) ) + 1;
		}
		
		$url = explode (self::$_url_dir_connector, substr ($url,  $_len ) );
		$_len = array_keys ($returnValue );
		for ($i = 0; $i < count($url ); $i++ ) {
			if (!preg_match ('~[^0-9a-z]~', $url[ $i ] ) ) {
				$returnValue [ $_len[ $i ] ] = $url [$i];
			}
			
			if ($i > 1 ) {
				break;
			}			
		}
		if (empty ($returnValue['block'] ) ) {
			$returnValue['block'] = $returnValue['app'];
		}
		
		if (!empty ($url[count($url) - 1] ) ) {
			$param = explode (self::$_url_param_connector, $url[count($url) - 1]);
			$returnValue['file'] = $param [0];
			for ($i = 1; $i < count ($param ); $i++) {
				$returnValue['param'][ $param[$i] ] = $param[ ++$i ];
			}
		}
		$returnValue['app_base_url'] = 'http://' . $_SERVER['HTTP_HOST'] . preg_replace ('~' . $_SERVER['PATH_INFO'] . '~i', '', $_SERVER['REQUEST_URI'] );

		return $returnValue;
	 }

	 /**
	  * 创建URL
	  *
	  * @data array = array ('block' => string, 'file' => string, 'data' => array () )
	  * @return string
	  */
	 static public function arr_url ($data) {
		$_config = taro::__config ();
		
		if (!is_array ($data ) ) {
			$data = array ();
		}
		
		$_data = array ('block', 'file' );
		foreach ($_data as $v ) {
			if (empty ($data[$v] )) {
				$data[ $v ] = $_config[ 'app_def_' . $v ];
			} else {
				$_use_data = false;
			}
		}
		
		$returnValue = array ();
		foreach ($data as $k => $v ) {
			if (in_array ((string)$k, $_data ) ) {
				continue;
			}
			
			if (is_array ($v ) ) {
				foreach ($v as $kk => $vv ) {
					$returnValue[$kk] = $vv;
				}
			} else {
				$returnValue[$k] = $v;
			}
			unset ($data[ $k ] );
		}
		$data['data'] = $returnValue;
		$returnValue = array ();
		
		if ($_config['app'] != $_config['app_def_app'] ) {		
			$returnValue[] = $_config['app'];
		}

		if ($data['block'] != $_config['app_def_block'] ) {		
			$returnValue[] = $data['block'];
		}
		
		$_data = array ();		
		$_data[] = $data['file'];
		if ($data['file'] != $_config['app_def_file'] ) {		
		//	$_data[] = $data['file'];
		}
		
		foreach ($data['data'] as $k => $v ) {
			$_data[] = $k . self::$_url_param_connector . $v;
		}
		
		if (count($_data ) ) {
			$returnValue[] = join (self::$_url_param_connector, $_data ) . $_config['app_suffix'];
		}
		
		if (substr($_config['app_base_url'], -1) != self::$_url_dir_connector ) {
			$_config['app_base_url'] .= self::$_url_dir_connector;
		}
		
		$returnValue = $_config['app_base_url'] . join (self::$_url_dir_connector, $returnValue );
		
		return $returnValue;
	 }
}
?>