<?php
namespace Waljqiang\Yunlot\Exception;
class YunlotException extends \Exception{
	const UNSUPPORT_YUNLOT_VERSION = 400000000;//不支持的协议版本
	const YUNLOT_PARAMS_ERROR = 400000001;//参数错误
	//v1.0
	const YUNLOT10_FORMAT_ERROR = 400010000;//协议格式错误
	const YUNLOT10_HEADER_FORMAT_ERROR = 400010001;//协议头格式错误
	const YUNLOT10_HEADER_TYPE_ERROR = 400010002;//协议头中type必须为0或1
	const YUNLOT10_HEADER_ENCODE_ERROR = 400010003;//协议紧支持明文和AES加密
	const YUNLOT10_DECODE_ERROR = 400010004;//AES解密失败
	const YUNLOT10_BODY_FORMAT_ERROR = 400010005;//BODY数据格式错误
	const YUNLOT10_BODY_SET_ERROR = 400010006;//设置body失败
}