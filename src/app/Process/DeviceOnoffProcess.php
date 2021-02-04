<?php
namespace app\Process;

use Server\Asyn\MQTT\Message\CONNACK;
use Server\Asyn\MQTT\Message\PUBLISH;
use Server\Asyn\MQTT\MQTT;
use Server\Asyn\Mysql\Miner;
use Server\Asyn\Mysql\MysqlAsynPool;
use Server\Asyn\Redis\RedisAsynPool;
use app\Utils\HashcltidsPool;
use Server\Components\Process\Process;
use Server\CoreBase\Child;
use Server\Memory\Pool;
use App\Services\CacheService;
use Carbon\Carbon;
/**
* Class DeviceUpReceiveProcess
* @package app\Process
*/
class DeviceOnoffProcess extends Process{
	const NAME = "device-onoff";
	/**
	* @var MQTT
	*/
	protected $mqtt;
	protected $redisPool;
	protected $mysqlPool;
	protected $cltidPool;

	protected $cacheService;
	/**
	* @var Miner
	*/
	public $db;
	/**
	* @var \Redis
	*/
	protected $redis;
	protected $redisPrefix;
	/**
	* @param $process
	* @throws \Server\Asyn\MQTT\Exception
	* @throws \Server\CoreBase\SwooleException
	*/
	public function start($process){
		/**
		* 添加各种连接池
		*/
		$this->redisPrefix = config('redis.prefix');
		$this->addAsynPools();
		$this->addServices();
		$mqtt = new MQTT($this->config->get("mqtt.address") . ":" . $this->config->get("mqtt.port"),"deviceonoff" . uniqid());
		//设置持久会话
		$mqtt->setConnectClean($this->config->get("mqtt.clean"));
		//认证
		$mqtt->setAuth($this->config->get("mqtt.username"),$this->config->get("mqtt.password"));
		//存活时间
		$mqtt->setKeepalive($this->config->get("mqtt.keepalive"));
		//回调
		//连接成功回调
		$mqtt->on('connack', function (MQTT $mqtt, CONNACK $connackObject){
			$this->connectCallback($mqtt, $connackObject);
		});
		//收到消息回调
		$mqtt->on('publish', function ($mqtt, PUBLISH $publishObject){
			try{
				$topic = $publishObject->getTopic();
				$message = json_decode($publishObject->getMessage());
				$cltHeader = $this->config->get("hashids.clt.header");
				if(substr($message->clientid,0,strlen($cltHeader)) == $cltHeader){
					list($userID,$productID,$mac) = decodeCltID($message->clientid);
					$action = substr($topic,strripos($topic,"/")+1);
					if($action == "connected" && $message->connack == 0){//上线
						$this->cacheService->parseStatus($mac,["status" => config("device.status.online")]);
					}elseif($action == "disconnected"){//下线
						$this->cacheService->parseStatus($mac,["status" => config("device.status.offline")]);
					}else{

					}
				}
			}catch(\Exception $e){
				logger($e->getMessage(),ERROR);
			}
		});
		
		$mqtt->connect();
		$this->mqtt = $mqtt;
	}
	/**
	* 发布消息
	* @param $name string 主题名
	* @param $msg string 消息
	* @throws \Server\Asyn\MQTT\Exception
	*/
	public function pub($name, $msg){
		$this->mqtt->publish($name, $msg);
	}
	protected function onShutDown(){
		$this->mqtt->disconnect();
		// TODO: Implement onShutDown() method.
	}
	/**
	* 异步连接池
	* @throws \Server\CoreBase\SwooleException
	*/
	private function addAsynPools(){
		$this->redisPool = new RedisAsynPool($this->config, $this->config->get('redis.active'));
		$this->mysqlPool = new MysqlAsynPool($this->config, $this->config->get('mysql.active'));
		$this->cltidPool = new HashcltidsPool($this->config);
		get_instance()->addAsynPool("redisPool", $this->redisPool);
		get_instance()->addAsynPool("mysqlPool", $this->mysqlPool);
		get_instance()->addAsynPool("cltidPool",$this->cltidPool);
		$this->db = get_instance()->getAsynPool('mysqlPool')->installDbBuilder();
		$this->redis = get_instance()->getAsynPool('redisPool')->getCoroutine();
	}

	private function addServices(){
		$this->cacheService = get_instance()->loader->model("app\Services\CacheService",$this);
	}
	/**
	* 连接成功回调
	* @throws \Throwable
	*/
	private function connectCallback(MQTT $mqtt, CONNACK $connack_object){
		$onTopic = '$queue/' . $this->config->get("mqtt.topic.online");//共享订阅
		$offTopic = '$queue/' . $this->config->get("mqtt.topic.offline");//共享订阅
		$qos = $this->config->get("mqtt.qos");
		$topics = [
			$onTopic => $qos,
			$offTopic => $qos
		];
		$mqtt->subscribe($topics);
	}
	/**
	* 消息处理
	* @param $mac string 设备MAC
	* @param $data string 消息
	* @throws \Server\CoreBase\SwooleException
	*/
	protected function receive($data){
		go(function () use ($data) {
			$child = Pool::getInstance()->get(Child::class);
			try {
				$this->redis->lpush($this->redisPrefix . CacheService::DEVICE_QUEQUE_DATA,json_encode($data,JSON_UNESCAPED_UNICODE));
			} catch (\Exception $e) {
				logger($e->getMessage()."MQTTH监听回调报错",ERROR);
				throw new \Exception($e->getMessage(),config("exceptions.MQTT_RECEIVE_ERROR"));
			}
			$child->destroy();
			Pool::getInstance()->push($child);
		});
	}

	private function getClientFromTopic($topic){
		try{
			$arr = explode("/",$topic);
			if(count($arr) != 3 || $arr[2] != "dev2app"){
				throw new \Exception("The topic format is error",config("exceptions.MQTT_TOPIC_ERROR"));
			}
			list($userID,$productID,$mac) = decodeCltID($arr[1]);
			return [$arr[0],$arr[1],$mac];
		}catch(\Exception $e){
			throw new \Exception($e->getMessage(),config("exceptions.MQTT_TOPIC_ERROR"));
		}
	}

}