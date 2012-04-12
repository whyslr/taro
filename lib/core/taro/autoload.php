<?php
/**
 * 自动加载类源文件
 *
 * @category core
 * @package taro
 * @author whyslr
 */
class core_taro_autoload {
	static protected $_instance;
	protected $_isIncludePathDefined = null;
	
	/**
	 * 构造函数
	 */
	public function __construct() {
	    $this->_isIncludePathDefined = defined('INCLUDE_PATH');
	}
	
	/**
	 * 构造函数
	 */
	private function core_taro_autoload () {
	}
	
	/**
	 * 静态构造函数
	 *
	 * @return core_taro_autoload
	 */
	static public function instance() {
	    if (!self::$_instance) {
	        self::$_instance = new core_taro_autoload();
	    }
	
	    return self::$_instance;
	}
	
	/**
	 * 注册自动加载函数
	 */
	static public function register() {
	    spl_autoload_register(array(self::instance(), 'autoload'));
	}
	
	/**
	 * 加载类源文件
	 *
	 * @param string $class
	 */
	public function autoload($class) {
        $classFile = str_replace(' ', DS, strtolower(str_replace('_', ' ', $class))) . '.php';

	    $returnValue = include ($classFile );
	    if (!$returnValue ) {
	    	$returnValue = include ($class . '.class.php' );
	    }
	
	    return $returnValue;
	}
}