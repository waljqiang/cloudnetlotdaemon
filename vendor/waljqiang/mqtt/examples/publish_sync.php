<?php
require_once __DIR__ .'/shared.php';
ini_set('error_log', __DIR__ . './runtime/mqtt.log');
use Waljqiang\Mqtt\Mqtt;

try{
	$mqtt = new Mqtt($config['address']);

	//$mqtt->setDebug(true,4);

	$mqtt->setClientID(uniqid());
	$mqtt->setAuth($config['username'],$config['password']);

	if(!$mqtt->connect()){
		die("Not Connected");
	}

	$c = 0;
	$msg = '[{"fun":2,"comm_id":"0002002157303185193","mac":["1"],"type":"2","params":{"wlan_channel":36,"vap":[{"vap_id":4,"vap_enable":1,"vap_hide_ssid":0,"vap_psk_key":"12345678!@#@#@!","vap_encmode":"9","vap_chiper":"7","vap_ssid":"\u5bb6\u5723\u8bde\u8282\u8fd4\u56de\u952e"}],"Now":1573031851}}]';
	for($i=0;$i < 100;$i++){
		$topics[$i] = 'sensor/dev/sub/' . $i;
	}

	$qos = 0;
	$retain = 0;
	do {
		$msg = '[{"fun":2,"comm_id":"0002005157302980513","mac":["1"],"type":"5","params":{"TimeRebootEnable":1,"TimeReboot_Time":"23","Now":1573029805}}]';
	    # mosquitto_sub -t 'sskaje/#'  -q 1 -h test.mosquitto.org
	    $rs = $mqtt->publish_sync($topics[$c], $msg, $qos, $retain);
	    echo "======== QoS={$qos}, Count={$c}\n";
	} while (++$c < 100);

}catch(\Exception $e){
	var_dump($e->getMessage());
}