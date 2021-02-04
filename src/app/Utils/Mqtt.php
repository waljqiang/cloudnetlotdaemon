<?php
namespace app\Utils;

use Waljqiang\Mqtt\Mqtt as Base;
use sskaje\mqtt\Message\Will;
use Waljqiang\Mqtt\MessageHandler;
use sskaje\mqtt\Exception\ConnectError;

class Mqtt extends Base{
	private $options;
	private $messageHandle;
	
	public function __construct($options){
		$this->options = $options;
		$address = "tcp://" . $this->options["address"] . ":" . $this->options["port"];
		parent::__construct($address);
        $this->setAuth($this->options['username'],$this->options['password']);
        $this->setConnectClean($this->options['clean']);
        $this->setKeepalive($this->options['keepalive']);
        $this->setRetryTimeout($this->options['timeout']);
        $this->messageHandle = new Handler();
	}

	public function setQos($qos=0){
		$this->options['qos'] = $qos;
	}

	public function setRetain($retain=0){
		$this->options['retain'] = $retain;
	}

	public function publishSync($topics,$message,$qos=NULL,$retain=NULL,&$msgid=NULL){
		$this->setClientID("devicedown" . uniqid());
		$this->connect();
		$qos = !is_null($qos) ? $qos : $this->options['qos'];
		$retain = !is_null($retain) ? $retain : $this->options['retain'];
		try{
			logger('mqtt sysnc message [' . $message . '] to topics [' . implode(',',$topics) . '] qos [' . $qos . '] retain [' . $retain . ']');
			foreach ($topics as $topic) {
				if(!$this->publish_sync($topic,$message,$qos,$retain,$msgid)){
					throw new \Exception("Mqtt publish failure",config('exceptions.MQTT_PUBLISH_ERROR'));
				}
			}
		}catch(\Exception $e){
			$this->disconnect();
			if($e instanceof ConnectError){
				throw new \Exception('Mqtt connect failure',config('exceptions.MQTT_CONNECT_ERROR'));
			}else{
				throw new \Exception("Mqtt publish failure",config('exceptions.MQTT_PUBLISH_ERROR'));
			}		
		}
		$this->disconnect();
	}

	public function publishAsync($topics, $message,$qos=NULL, $retain=NULL,$callBack=NULL,&$msgid=NULL){
		$this->setClientID("devicedown" . uniqid());
		try{
			$this->connect();
			$qos = !is_null($qos) ? $qos : $this->options['qos'];
			$retain = !is_null($retain) ? $retain : $this->options['retain'];
		
			logger('mqtt async message [' . $message . '] to topics [' . implode(',',$topics) . '] qos [' . $qos . '] retain [' . $retain . ']');
			if(!empty($callBack)){
				$this->messageHandle->setCallBack($callBack);
				foreach ($topics as $topic) {
					$rs = $this->publish_async($topic,$message,$qos,$retain,$msgid);
					$this->messageHandle->waitQueue[$rs['msgid']] = $rs;
				}
				$this->setHandler($this->messageHandle);
				$this->loop();
			}else{
				foreach ($topics as $topic) {
					$this->publish_async($topic,$message,$qos,$retain,$msgid);
				}
			}
		}catch(\Exception $e){
			$this->disconnect();
			if($e instanceof ConnectError){
				throw new \Exception('Mqtt connect failure',config('exceptions.MQTT_CONNECT_ERROR'));
			}else{
				throw new \Exception("Mqtt publish failure",config('exceptions.MQTT_PUBLISH_ERROR'));
			}		
		}
		$this->disconnect();
	}

}

class Handler extends MessageHandler{
	private $callBack;

	public function setCallBack($callBack){
		foreach ($callBack as $key => $value) {
			if(in_array($key,['onConnack','onDisconnect','onSuback','onUnsuback','onPublish','onPuback','onPubrec','onPubrel','onPubcomp','onPingresp'])){
				$this->callBack[$key] = $value;
			}
		}
	}

    public function onPuback($mqtt, $object){
    	unset($this->waitQueue[$object->getMsgID()]);
    	$this->call('onPuback',$mqtt,$object);
    }

    private function call($key,$mqtt,$object){
    	if(isset($this->callBack[$key])){
    		$this->callBack[$key]($mqtt,$object);
    	}
    }
}