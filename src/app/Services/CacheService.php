<?php
namespace app\Services;

use Carbon\Carbon;

class CacheService extends BaseService{
	/**
	 * 需要地址定位的设备队列
	 */
	const DEVICE_QUEQUE_LOCATION = 'device:queue:location';
	/**
	 * 设备上报信息队列
	 */
	const DEVICE_QUEQUE_DATA = 'device:queue:data';
	/**
	 * 设备信息缓存
	 */
	const DEVICE_DYNAMIC = 'h:device:dynamic:';
	/**
	 * 设备注册信息,当产品发生改变时需要清除
	 */
	const REGISTER = 'register:';
	/**
	 * 消息ID缓存，用于判断重复消息
	 */
	const MSG_MID = 'msg:';
	private $prefix;

	public function initialization(&$context){
		parent::initialization($context);
		$this->prefix = config('redis.prefix');
	}

	public function setLocation($data){
		return $this->redis->lpush($this->prefix . self::DEVICE_QUEQUE_LOCATION,json_encode($data,JSON_UNESCAPED_UNICODE));
	}

	public function getLocation(){
		return json_decode($this->redis->rpop($this->prefix . self::DEVICE_QUEQUE_LOCATION),true);
	}

	public function getUpinfo($num = 10){
		$res = [];
		$datas = $this->redis->evalSha(getLuaSha1('rpops_from_count'), [$this->prefix . 'device:queue:data',$num],2,[1,2,3]);
		foreach ($datas as $data) {
			if(!empty($data)){
				$res[] = json_decode($data,true);
			}else{
				break;
			}
		}
		return $res;
		
	}

	public function setRegister($prtid,$mac,$data,$ttl=0){
		return !empty($ttl) ? $this->redis->setex($this->prefix . self::REGISTER . $prtid . ":" . $mac,$ttl,json_encode($data)) : $this->redis->set($this->prefix . self::REGISTER . $prtid . ":" . $mac,json_encode($data));
	}

	public function getRegister($prtid,$mac){
		$rs = $this->redis->get($this->prefix . self::REGISTER . $prtid . ":" . $mac);
		return $rs ? json_decode($rs,true) : [];
	}

	public function delRegister($prtid,$mac){
		return $this->redis->del($this->prefix . self::REGISTER . $prtid . ":" . $mac);
	}

	//返回删除个数
	public function clearRegisterByPrtid($prtid){
		return $this->redis->evalSha(getLuaSha1('clear_register_by_prtid'),[$this->prefix . self::REGISTER . $prtid],1);
	}

	public function setDeviceDynamic($mac,$data){
		return $this->redis->hmset($this->prefix . self::DEVICE_DYNAMIC . $mac,$data);
	}

	public function setDevicesDynamic($macs,$data){
		$this->redis->pipeline(function($pipe) use ($macs,$data){
			foreach ($macs as $mac) {
				$pipe->hmset($this->prefix . self::DEVICE_DYNAMIC . $mac,$data);
			}
		});
		return true;
	}

	public function setDevicesDynamics($datas){
		$this->redis->pipeline(function($pipe) use ($datas){
			foreach ($datas as $mac => $data) {
				$pipe->hmset($this->prefix . self::DEVICE_DYNAMIC . $mac,$data);
			}
		});
		return true;
	}

	public function getDeviceDynamic($mac){
		$data = $this->redis->hgetall($this->prefix . self::DEVICE_DYNAMIC . $mac);
		if(empty($data)){
			$this->setDeviceDynamic($mac,[
				"cpu_use" => "0",
				"memory_use" => "0",
				"runtime" => "0",
				"status" => "0",
				"link" => "-1",
				"rssi" => "-1"
			]);
		}
		return !empty($data) && isset($data["status"]) && $data["status"] == config("device.status.online") ? $data : ["cpu_use" => "0","memory_use" => "0","runtime" => "0","status" => "0","link" => "-1","rssi" => "-1"];
	}

	public function getDeviceDynamicWithField($mac,$field){
		$rs = $this->redis->hget($this->prefix . self::DEVICE_DYNAMIC . $mac,$field);
		return !empty($rs) ? $rs : "";
	}

	public function getDevicesDynamic($macs){
		$res = [];
		$initMacs = [];
		if(!empty($macs)){
			$results = $this->redis->pipeline(function($pipe)use($macs){
				foreach ($macs as $mac) {
					$pipe->hgetall($this->prefix . self::DEVICE_DYNAMIC . $mac);
				}
			});
			foreach ($macs as $key => $mac) {
				if(empty($results[$key])){
					$initMacs[] = $mac;
				}
				$res[$mac] = !empty($results[$key]) && $results[$key]["status"] == config("device.status.online") ? $results[$key] : ["cpu_use" => "0","memory_use" => "0","runtime" => "0","status" => "0","link" => "-1","rssi" => "-1"];
			}
			if(!empty($initMacs)){
				$this->setDevicesDynamic($initMacs,[
					"cpu_use" => "0",
					"memory_use" => "0",
					"runtime" => "0",
					"status" => "0",
					"link" => "-1",
					"rssi" => "-1"
				]);
			}
		}
		return $res;
	}

	public function parseStatus($mac,$data,$time = ""){
		$time = !empty($time) ? $time : Carbon::now()->timestamp;
		//更新设备CPU使用率、内存使用率、运行时间
		$oldCache = $this->getDeviceDynamic($mac);
		$newStatus = isset($data["status"]) ? $data["status"] : config("device.status.online");
		
		if(isset($oldCache["status"]) && $oldCache["status"] == config("device.status.online")){
			if($newStatus == config("device.status.online")){//在线->在线
				logger("handle status:online------>online",DEBUG);
				$cacheData = array_intersect_key($data,["cpu_use" => "","memory_use" => "","runtime" => ""]);
			}else{//在线->离线
				logger("handle status:online------->offline",DEBUG);
				$cacheData = ["cpu_use" => "","memory_use" => "","runtime" => "","status" => config("device.status.offline"),"offline_time" => $time];
			}
		}else{
			if($newStatus == config("device.status.online")){//离线->在线
				logger("handle status:offline------->online",DEBUG);
				$cacheData = array_merge(array_intersect_key($data,["cpu_use" => "","memory_use" => "","runtime" => ""]),["status" => config("device.status.online"),"online_time" => $time]);
			}else{//离线->在线
				logger("handle status:offline------->offline",DEBUG);
				$cacheData = ["cpu_use" => "","memory_use" => "","runtime" => ""];
			}
		}

		if(isset($data["parent"]["mac"]) && !empty($data["parent"]["mac"])){
			$cacheData = array_merge($cacheData,[
				"parent" => $data["parent"]["mac"],
				"link" => isset($data["parent"]["link"]) ? $data["parent"]["link"] : "-1",
				"rssi" => isset($data["parent"]["rssi"]) ? $data["parent"]["rssi"] : "-1"
			]);
		}
		$this->setDeviceDynamic($mac,$cacheData);
	}

	public function __call($method,$args){
		return json_decode(call_user_func_array([$this->redis,$method],$args),true);
	}

}