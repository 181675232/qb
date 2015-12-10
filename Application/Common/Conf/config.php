<?php
return array(
// 	'配置项'=>'配置值'
	'SESSION_AUTO_START' => true, //是否开启session
	'TMPL_CACHE_ON'=>false,      // 默认开启模板缓存
// 	禁止模块访问
// 	'MODULE_DENY_LIST'=>array('Home','Common','Runtime'),
//	允许模块访问
	'MODULE_ALLOW_LIST'=>array('Admin','Api'),
//	设置默认加载模块
	'DEFAULT_MODULE'=>'Admin',
//	减少目录结构
	'TMPL_FILE_DEPR'=>'_',
//	URL模式
	'URL_MODEL' => 2,
//	URL大小写
	'URL_CASE_INSENSITIVE' => true,
//  数据库
	'DB_TYPE'               =>  'mysql',     // 数据库类型
	'DB_HOST'               =>  '101.200.81.192', // 服务器地址
	'DB_NAME'               =>  'php8082',          // 数据库名 
	'DB_USER'               =>  'root',      // 用户名
	'DB_PWD'                =>  '1qaz2wsx',          // 密码
	'DB_PORT'               =>  '3306',        // 端口
	'DB_PREFIX'             =>  't_',    // 数据库表前缀
	'DB_CHARSET'			=>  'utf8',      // 数据库编码
	'DB_DEBUG'				 =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志

	
	'SHOW_PAGE_TRACE' => false,		//页面trace
	
// 	'DEFAULT_V_LAYER' => 'view',	//修改视图目录	
// 	'TMPL_TEMPLATE_SUFFIX' =>  '.tpl',//修改后缀
// 	'VIEW_PATH'=>'./Public/', //修改模板目录
);