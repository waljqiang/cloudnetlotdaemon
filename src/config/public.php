<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */

/**
 * 选择数据库环境
 */
return [
	'public' => [
		'iplocation' => [
			'china' => env('APP_LANG','zh') == 'zh' ? '中国' : 'China',
			'sleep' => 300000,//进程睡眠时间,单位毫秒
			'baiduapi' => env('LOCATION_BAIDU_API','http://api.map.baidu.com/location/ip?ak=4itF2ygdKkIfshFlQggs7DZA&ip=%s&coor=gcj02'),//http://lbsyun.baidu.com/index.php?title=webapi/ip-api
			'ipapi' => env('LOCATION_IP_API','http://ip-api.com/json/%s?lang=%s&fields=status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,query')//https://ip-api.com/docs
		],
		'process' => [
			'defined' => [
				"app\Process\DeviceUpReceiveProcess" => 1,//接收设备上行数据
	            "app\Process\DeviceUpHandleMasterProcess" => 1,//上行数据处理主进程
	            "app\Process\DeviceUpHandleChildProcess" => 5,//上行数据处理子进程
	            "app\Process\DeviceOnoffProcess" => 1,//设备上下线
	            "app\Process\IPLocationProcess" => 1,//IP定位
			],
			'upinfo' => [
				'sleep' => 1000,//进程睡眠时间,单位毫秒
				'handle_num' => 100
			]
		],
		'cache' => [
			'msgkeyttl' => 36000,//10小时过期,
			'registerttl' => 3600*24*30,//1个月过期
		],
		'up_number' => 50,
		'statics' => [
			'retry' => 3,
			'handle_num' => 1000
		]
	]
];
