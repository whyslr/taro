<?php
/**
 * 模版解析类
 *
 * @category core
 * @package taro
 * @author whyslr
 */
class core_taro_tpl {
	/**
	 * 模版解析器
	 */
	private static $tpl = null;
	
	/**
	 * 模版变量值
	 */
	private static $assign = null;
	
	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct () {
	}

	/**
	 * 构造函数
	 *
	 * @return void
	 */
	private function core_taro_tpl () {
	}
	
	/**
	 * 绑定模版变量
	 *
	 * @return $this
	 */
	public function assign ($name, $value) {
		$returnValue = &$this;
		
		$this->assign[$name] = $value;
		
		return $returnValue;
	}
	
	/**
	 * 编译模版
	 *
	 * @tpl_file string
	 * @return void
	 */
	public function display ($tpl_file = '') {
		if (empty ($tpl_file ) || !is_string ($tpl_file ) ) {
			die ('occurs error (' . __CLASS__ . ' line ' . __LINE__ . ')' );
		}
		
		if (is_null (self::$tpl ) ) {
			$this->tpl = new core_tpl_smarttemplate ( );
			$_config = taro::__config ();
			$this->tpl->template_dir = APP_ROOT . $_config['app_root'] . DS . $_config['app_tpl_dir'];
			$this->tpl->temp_dir = APP_ROOT . $_config['app_root'] . DS . $_config['app_cache_dir'] . DS . 'tpl';
			$this->tpl->cache_dir = $this->tpl->temp_dir;
		}
		
		$this->tpl->set_templatefile ($tpl_file );
		$this->tpl->output ($this->assign );
		$this->assign = null;
	} 
}
?>