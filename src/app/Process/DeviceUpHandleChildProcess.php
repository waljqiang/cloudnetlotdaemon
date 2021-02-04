<?php
namespace app\Process;

use Server\Components\Process\Process;
use Server\Asyn\Redis\RedisAsynPool;
use Server\Asyn\Mysql\MysqlAsynPool;
use Server\Memory\Pool;
use Server\CoreBase\Child;
use app\Utils\YunlotPool;
use app\Utils\MqttClientPool;
use app\Utils\HashbindPool;
use app\Utils\HashprtidsPool;
use app\Utils\HashcltidsPool;

class DeviceUpHandleChildProcess extends Process{
	const NAME = "deviceup-handle-child";
	private $cacheService;
	private $mqttService;

	public function start($process){
		$this->addAsynPools();
		$this->addServices();
	}

	public function onShutDown(){
		
	}

	/**
	 * 功能描述
	 *
	 * @oneWay
	 */
	public function handleData($data){
		try{
			logger("Handle the data " . json_encode($data) . "]",DEBUG);
			$this->mqttService->handle($data["prtid"],$data["cltid"],$data["mac"],$data["data"]);
			return get_instance()->getWorkerId();
		}catch(\Exception $e){
			return;
		}
	}

	private function addAsynPools(){
		get_instance()->addAsynPool("redisPool",new RedisAsynPool($this->config,$this->config->get('redis.active')));
		get_instance()->addAsynPool("mysqlPool",new MysqlAsynPool($this->config,$this->config->get('mysql.active')));
		get_instance()->addAsynPool("yunlotPool",new YunlotPool($this->config));
		get_instance()->addAsynPool("mqttClientPool",new MqttClientPool($this->config));
		get_instance()->addAsynPool("bindPool",new HashbindPool($this->config));
		get_instance()->addAsynPool("prtidPool",new HashprtidsPool($this->config));
		get_instance()->addAsynPool("cltidPool",new HashcltidsPool($this->config));
	}

	private function addServices(){
		$this->cacheService = get_instance()->loader->model("app\Services\CacheService",$this);
		$this->mqttService = get_instance()->loader->model("app\Services\MqttService",$this);
	}

}