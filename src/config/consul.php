<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-3-10
 * Time: 下午5:58
 */
$startJoin = explode(',',env('CONSUL_START_JOIN','127.0.0.1'));
$watches = !empty(env('CONSUL_WATCHES','')) ? explode(',',env('CONSUL_WATCHES','')) : [];
$services = !empty(env('CONSUL_SERVICES','')) ? explode(',',env('CONSUL_SERVICES','')) : [];
return [
	'consul' => [
		'enable' => env('CONSUL_ENABLE',FALSE),//是否启用consul
		'datacenter' => env('CONSUL_DATACENTER','cloudnetlot'),//数据中心配置
		'client_addr' => env('CONSUL_CLIENT_ADDR','127.0.0.1'),//开放给本地
		'leader_service_name' => env('CONSUL_LEADER','cloudnetlotdaemon'),//服务器名称，同种服务应该设置同样的名称，用于leader选举
		'node_name' => env('CONSUL_NODE_NAME',''),//node的名字，每一个都必须不一样,也可以为空自动填充主机名
		'data_dir' => env('CONSUL_DATA_DIR','/tmp/consul'),//默认放在临时文件下
		'start_join' => $startJoin,//join地址，可以是集群的任何一个，或者多个
		'bind_net_dev' => env('CONSUL_BIND_NET_DEV','enp2s0'),//本地网卡设备
		'watches' => $watches,//监控服务
		'services' => $services,//发布服务
	],
	'cluster' => [
		'enable' => env('CONSUL_CLUSTER_ENABLE',TRUE),//是否开启TCP集群,启动consul才有用
		'port' => env('CONSUL_CLUSTER_PORT',9999),//TCP集群端口
	],
	'fuse' => [
		'threshold' => env('CONSUL_FUSE_THRESHOLD',0.01),//阀值
		'checktime' => env('CONSUL_FUSE_CHECKTIME',2000),//检查时间
		'trytime' => env('CONSUL_FUSE_TRYTIME',1000),//尝试打开的间隔
		'trymax' => env('CONSUL_FUSE_TRYMAX',3)//尝试多少个
	]
];