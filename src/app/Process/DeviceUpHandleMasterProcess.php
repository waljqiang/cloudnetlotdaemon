<?php
namespace app\Process;

use Server\Components\Process\Process;
use Server\Asyn\Redis\RedisAsynPool;
use Server\Asyn\Mysql\MysqlAsynPool;
use Server\Memory\Pool;
use Server\CoreBase\Child;
use app\Utils\YunlotPool;
use app\Utils\MqttClientPool;
use Server\Components\Process\ProcessManager;
use app\Services\CacheService;

class DeviceUpHandleMasterProcess extends Process{
	const NAME = "deviceup-handle-master";
	private $cacheService;

	public function start($process){
		$this->addAsynPools();
		$this->addServices();
		while(true){
			$datas = $this->cacheService->getUpinfo($this->config->get('public.process.upinfo.handle_num'));
			if(!empty($datas)){
				foreach($datas as $data){
					ProcessManager::getInstance()->getRpcCall(DeviceUpHandleChildProcess::class,false,DeviceUpHandleChildProcess::NAME . rand(1,$this->config->get('public.process.defined.app\Process\DeviceUpHandleChildProcess')))->handleData($data);
				}
			}else{
				sleepCoroutine(config('public.process.upinfo.sleep'));
			}	
		}
	}

	public function onShutDown(){
		
	}

	private function addAsynPools(){
		get_instance()->addAsynPool("redisPool",new RedisAsynPool($this->config,$this->config->get('redis.active')));
		get_instance()->addAsynPool("mysqlPool",new MysqlAsynPool($this->config,$this->config->get('mysql.active')));
		get_instance()->addAsynPool("yunlotPool",new YunlotPool($this->config));
		get_instance()->addAsynPool("mqttClientPool",new MqttClientPool($this->config));
	}

	private function addServices(){
		$this->cacheService = get_instance()->loader->model(CacheService::class,$this);
	}

}