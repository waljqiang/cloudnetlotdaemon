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
	"exceptions" => [
		//system
	    "SUCCESS" => 10000,//成功
	    "ERROR" => 10001,//错误

	    //common
	    "HTTP_REQUEST_NO_EXISTS" => 600000100,//请求不存在
	    "HTTP_NO_ALLOWED_METHOD" => 600000101,//请求方法不允许
	    "THROTTLE_ERROR" => 600000102,//请求过于频繁,

	    //license
	    "LICENSE_NO" => 600100100,//没有license
	    "LICENSE_INVALID" => 600100101,//license invalid
	    "LICENSE_EXPIRE_IN" => 600100102,//license expired

	    //mqtt
	    "MQTT_RECEIVE_ERROR" => 600101100,//mqtt监听回调错误
	    "MQTT_CONNECT_ERROR" => 600101101,//mqtt连接失败
	    "MQTT_PUBLISH_ERROR" => 600101102,//发布消息失败
	    "MQTT_TOPIC_ERROR" => 600101103,//主题错误
	    "CLT_ERROR" => 600500130,//客户端id错误
	    "PRTID_ERROR" => 600500131,//产品ID错误

	    //mysql
	    "MYSQL_EXEC_ERROR" => 600102100,//mysql语句执行错误

	    //yunlot
	    "YUNLOT_PARSE_FAILURE" => 600200100,//yunlot协议解析失败
	    "YUNLOT_UPINFO_ERROR" => 600200101,//上报数据错误

	    "DEV_NO_CONNECT" => 600400174,//设备没有连接云平台
	    "BINDCODE_ERROR" => 600400177,//绑定码错误
	    "DEV_BINDED" => 600400178,//设备已绑定其他用户

	    "PRT_NO" => 600500125,//产品不存在
	    "PRT_STATUS_NO_ALLOW" => 600500126,//产品状态不允许
	]
];
