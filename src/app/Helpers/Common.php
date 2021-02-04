<?php
use Carbon\Carbon;

function config($key,$value=""){
	return get_instance()->config->get($key,$value);
}

function logger($message,$level=DEBUG){
	switch ($level) {
		case \Monolog\Logger::DEBUG:
			$prefix = 'DEBUG';
			break;
		case \Monolog\Logger::INFO:
			$prefix = 'INFO';
			break;
		case \Monolog\Logger::NOTICE:
			$prefix = 'NOTICE';
			break;
		case \Monolog\Logger::WARNING:
			$prefix = 'WARNING';
			break;
		case \Monolog\Logger::ERROR:
			$prefix = 'ERROR';
			break;
		case \Monolog\Logger::CRITICAL:
			$prefix = 'CRITICAL';
			break;
		case \Monolog\Logger::ALERT:
			$prefix = 'ALERT';
			break;
		case \Monolog\Logger::EMERGENCY:
			$prefix = 'EMERGENCY';
			break;
		default:
			$prefix = 'DEBUG';
			break;
	}
	get_instance()->log->addRecord($level,'[' . $prefix . ']' . $message . PHP_EOL);
}

function getRandomStr($length = 16){
	$str = "";
	$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	$max = strlen($str_pol) - 1;
	for ($i = 0; $i < $length; $i++) {
		$str .= $str_pol[mt_rand(0, $max)];
	}
	return $str;
}

//根据时间戳、时区、夏令时将时间转换为相应的时区时间gmdate
function convUnixToZoneGm($timestamp,$timeZone,$summerTime){
    return Carbon::createFromFormat("Y-m-d H:i:s",date('Y-m-d H:i:s',$timestamp))->addMinutes($timeZone * 60)->addMinutes
    ($summerTime * 60)->toDateTimeString();
}

//根据时间、时区、夏令时将时间转换为相应的时区时间gmdate
function convDateToZoneGm($date,$timeZone,$summerTime){
    return Carbon::createFromFormat("Y-m-d H:i:s",$date)->addMinutes($timeZone * 60)->addMinutes($summerTime * 60)->toDateTimeString();
}

//mac地址添加冒号
function setMac($mac){
    return substr($mac, 0, 2) . ':' . substr($mac, 2, 2) . ':' . substr($mac, 4, 2) . ':' . substr($mac, 6, 2) . ':' . substr($mac, 8, 2) . ':' . substr($mac, 10, 2);
}

//mac地址去除冒号
function parseMac($mac){
    return str_replace(':', '', $mac);
}
//生成协议类型
function getLotType($value){
    return strtoupper(str_pad(dechex($value),4,"0",STR_PAD_LEFT));
}
//生成信息类型
function getCommType($value){
    return strtoupper(str_pad(dechex($value),4,"0",STR_PAD_LEFT));
}
//生成命令ID
function getCommID($lotType,$commType,$time = ""){
    return !empty($time) ? getLotType($lotType) . getCommType($commType) . $time . getStr(4) : getLotType($lotType) . getCommType($commType) . time() . getStr(4);
}
//生成命令
function getCommand($commType,$body,$timestamp = "",$commID = ""){
    $timestamp = empty($timestamp) ? (string) time() : (string) $timestamp;
    if(empty($commID)){
        $body = array_merge(["comm_id" => getCommID(config("yunlot.lottype.down"),$commType,$timestamp)],$body);
    }else{
        $body = array_merge(["comm_id" => $commID],$body);
    }
    return get_instance()->getAsynPool("yunlotPool")->init()->setHeader(["type" => config("yunlot.lottype.down")])->setBody($body)->setNow($timestamp)->out();
}

function decodePrtID($encrypt){
    $prtIDArr = get_instance()->getAsynPool("prtidPool")->decodeHash($encrypt);
    if(count($prtIDArr) != 1){
        throw new \Exception("The prtid is error",config("exceptions.PRTID_ERROR"));
    }
    $userIDLength = intval(substr($prtIDArr[0],-2));
    return [substr($prtIDArr[0],0,$userIDLength)];
}

function decodeCltID($encrypt){
    $cltIDArr = get_instance()->getAsynPool("cltidPool")->decodeHash($encrypt);
    if(count($cltIDArr) != 1){
        throw new \Exception("The cltid is error",config("exceptions.CLT_ERROR"));
    }

    $macdecLength = intval(substr($cltIDArr[0],-2));
    $productIDLength = intval(substr($cltIDArr[0],-4,-2));
    $userIDLength = intval(substr($cltIDArr[0],-6,-4));
    return [
        substr($cltIDArr[0],0,$userIDLength),//用户ID
        substr($cltIDArr[0],$userIDLength,$productIDLength),//产品ID
        strtoupper(setMac(dechex(substr($cltIDArr[0],($userIDLength+$productIDLength),$macdecLength))))//mac地址
    ];
}


function generateClitid($userID,$productID,$mac,$time = ""){
    $time = empty($time) ? time() : $time;
    $macdec = hexdec(parseMac($mac));
    $userIDLength = str_pad(strlen($userID),2,0,STR_PAD_LEFT);
    $productIDLength = str_pad(strlen($productID),2,0,STR_PAD_LEFT);
    $str = $userID . $productID . $macdec . $time . $userIDLength . $productIDLength;
    return app("Hashcltids")->encodeHash($str);
}

function parseBindCode($bind,$key){
    try{
        $result = "";
        $bind = base64_decode($bind);
        $keyLen = strlen($key);
        for($i=0;$i<strlen($bind);$i++){
            $k = $i % $keyLen;
            $result .= $bind[$i] ^ $key[$k];
        }
        $arr = explode("#", $result);
        return [
            $arr[0],//toUid
            strtoupper(setMac($arr[1])),//mac
            $arr[2],//gid
            $arr[3]//created_at
        ];
    }catch(\Exception $e){
        throw new \Exception("The bind code is error",config("exceptions.BINDCODE_ERROR"));
    }
}

//生成topic
function getTopic($prtid,$cltid){
    return sprintf(str_replace("+","%s",config("mqtt.topic.devicedown")),$prtid,$cltid);
}

function sendToMqtt($topics, $content,$qos = 0, $retain = 0,$callBack = NULL,$isAsync = true,&$msgID = Null){
    try{
        $mqttClient = get_instance()->getAsynPool("mqttClientPool");
        $isAsync ? $mqttClient->publishAsync($topics,$message,$qos,$retain,$callBack,$msgID) : $mqttClient->publishSync($topics,$message,$qos,$retain,$msgID);
        return true;
    }catch(\Exception $e){
        throw new \Exception($e->getMessage(),config("exceptions.MQTT_PUBLISH_ERROR"));
        return false;
    }
}