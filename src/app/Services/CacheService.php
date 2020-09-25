<?php
namespace app\Services;

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

	public function setDeviceDynamic($mac,$data){
		return $this->redis->hmset($this->prefix . self::DEVICE_DYNAMIC . $mac,$data);
	}

	public function getDeviceDynamic($mac){
		return $this->redis->hgetall($this->prefix . self::DEVICE_DYNAMIC . $mac);
	}

	public function __call($method,$args){
		return json_decode(call_user_func_array([$this->redis,$method],$args),true);
	}

}