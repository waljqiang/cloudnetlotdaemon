<?php
namespace app\Process;

use Server\Components\Process\Process;
use Server\Asyn\Redis\RedisAsynPool;
use Server\Asyn\Mysql\MysqlAsynPool;
use Server\Memory\Pool;
use Server\CoreBase\Child;
use app\Utils\GuzzleHttpClientPool;

class IPLocationProcess extends Process{
	const NAME = "iplocation";
	private $cacheService;
	private $deviceService;

	public function start($process){
		$this->addAsynPool();
		$this->addService();
		while(true){
			$data = $this->cacheService->getLocation();
			if(!empty($data)){
				logger("start location the device[" . $data["mac"] . "]");
				$this->handleData($data);
			}else{
				sleepCoroutine(config('public.iplocation.sleep'));
			}			
		}
	}

	public function onShutDown(){
		
	}

	public function handleData($data){
		$this->deviceService->locationWithIP($data);
	}

	private function addAsynPool(){
		get_instance()->addAsynPool("redisPool",new RedisAsynPool($this->config,$this->config->get('redis.active')));
		get_instance()->addAsynPool("mysqlPool",new MysqlAsynPool($this->config,$this->config->get('mysql.active')));
		get_instance()->addAsynPool("GetIPAddress",new GuzzleHttpClientPool($this->config));
	}

	private function addService(){
		$this->cacheService = get_instance()->loader->model("app\Services\CacheService",$this);
		$this->deviceService = get_instance()->loader->model("app\Services\DeviceService",$this);
	}

}