<?php
/**
 * PHP环境版本检测
 */
if (version_compare(phpversion(), '5.3.0', '<') === true) {
	header ('Content-Type:text/html; charset=utf-8');
	die('<div style="font:12px/1.35em arial, helvetica, sans-serif;"><div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;"><h3 style="margin:0; font-size:1.7em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">PHP 版本太低啦，跪求5.3.*版本</h3></div></div>' );
}

/**
 * 目录分隔符
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * INCLUDE PATH 分隔符
 */
define('PS', PATH_SEPARATOR);

/**
 * 系统根目录
 */
define ('SYS_ROOT', dirname(__FILE__) . DS);

/**
 * 项目根目录
 */ 
if (!defined ('APP_ROOT' ) ) {
	define ('APP_ROOT', SYS_ROOT . 'app' . DS );
}

/**
* 系统项目设置
*/
include_once ('config.php');
define ('SYS_APP', serialize ($_CONFIG ) );
if (!SYS_APP ) {
	die ('坑爹的项目设置参数,请认真填写' );
} else {
	unset ($_CONFIG );
}

/**
 * 出错报告等级
 */
error_reporting(SYS_ERR_LEV);


/**
 * 项目根目录
 */ 
if (!defined ('APP_ROOT' ) ) {
	define ('APP_ROOT', SYS_ROOT . 'app' . DS );
}
if (!file_exists (APP_ROOT) ) {
	$app_path = explode (DS, str_replace (SYS_ROOT, '', APP_ROOT ) );
	$exists_path = SYS_ROOT; 
	foreach ($app_path as $kk => $vv ) {
		if (strlen ($vv ) ) {
			mkdir ($exists_path . DS . $vv );
			$exists_path .= $vv . DS;
		}
	}
}

/**
 * 加载项目入口文件
 */
include_once (SYS_ROOT . 'lib' . DS . 'taro.php');
?>