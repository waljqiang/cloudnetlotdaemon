<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */


return [
	'catCache' => [
		'enable' => env('CATCACHE_ENABLE',TRUE),
		'auto_save_time' => env('CATCACHE_AUTO_SAVE_TIME',1000),//自动存盘时间
		'save_dir' => env('CATCACHE_SAVE_DIR',BIN_DIR . '/cache/'),//落地文件夹
		'delimiter' => '.'
	]
];
