<?php
/**
 * 错误报告等级
 */
define ('SYS_ERR_LEV', E_ALL & ~E_NOTICE );

###项目demo运行设置开始###
/**
 * 根目录 
 */
$_CONFIG['demo']['app_root'] = 'demo';

/**
 * 代码文件存放目录
 */
$_CONFIG['demo']['app_dirs'] = 'front,admin';

/**
 * 项目URL自定义后缀
 */
$_CONFIG['demo']['app_suffix'] = '.html';
 
/**
 * 模版文件存放目录
 */
$_CONFIG['demo']['app_tpl_dir'] = 'template';

/**
 * 项目缓存文件存放目录
 */
$_CONFIG['demo']['app_cache_dir'] = 'cache';
###项目demo运行设置结束###
?>