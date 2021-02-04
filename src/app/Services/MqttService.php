<?php
namespace app\Services;

use app\Models\DeviceModel;
use Carbon\Carbon;


class MqttService extends BaseService{
	const SYSTEM = 'system';
	const NETWORK = 'network';
	const WIFI = 'wifi';
	const USER = 'user';
	const TIMEREBOOT = 'time_reboot';
	const CHILD = 'child';
	const COMMRESULT = 'comm_result';

	private $cacheService;
	private $deviceModel;
	private $yunlotPool;

	public function initialization(&$context){
		parent::initialization($context);
		$this->deviceModel = $this->loader->model(DeviceModel::class,$this);
		$this->cacheService = $this->loader->model(CacheService::class,$this);
		$this->yunlotPool = get_instance()->getAsynPool("yunlotPool")->init();	
	}

	public function handle($prtid,$cltid,$mac,$data){
		try{
			$yunData = $this->yunlotPool->parse($data);
			if($yunData->getHeader("type") != 1){
				throw new \Exception("the type of the upinfo header is error",config("exceptions.YUNLOT_UPINFO_ERROR"));	
			}
			$bind = $yunData->getHeader("bind");
			list($toUid,$devMac,$gid) = parseBindCode($bind,parseMac($mac));
			$this->checkData($prtid,$cltid,$mac,$bind,$toUid);
			$this->handleData($prtid,$cltid,$mac,$toUid,$gid,$yunData->getBody(),$yunData->getNow());
		}catch(\Exception $e){
			logger("code[" . $e->getCode() . "]message[".$e->getMessage() . "]",ERROR);
			//回错误消息给设备
			$topic = getTopic($prtid,$cltid);
			$time = Carbon::now()->timestamp;
			$commid = getCommID(config("yunlot.lottype.down"),config("device.typeinfo.upinfofail"),$time);
			$body = [
				"comm_id" => $commid,
				"command" => [
					"type" => "upinfofail",
					"faildata" => $data
				]
			];
			$command = getCommand(config("device.typeinfo.upinfofail"),$body,$time);
			sendToMqtt([$topic],$command);
		}
		
	}

	private function handleData($prtid,$cltid,$mac,$toUid,$gid,$data,$time){
		$res = [];
		if(!empty($data)){
			$device = $this->deviceModel->getDevices($mac);
			if(!empty($device["user_id"]) && $toUid != $device["user_id"]){//如果设备已绑定，则不能更改绑定用户
				throw new \Exception("The device is binded to another user",config('exceptions.DEV_BINDED'));
			}
			$this->db->begin(function() use ($prtid,$cltid,$mac,$toUid,$gid,$data,$time,$device){
				foreach($data as $key => $value){
					logger("Start handle {$key} data of the device[" . $mac . "]",DEBUG);
					switch ($key) {
						case self::SYSTEM:
							$this->handleSystem($device,$toUid,$gid,$value,$time);
							break;
						case self::NETWORK:
							$this->handleNetwork($device,$value,$time);
							break;
						case self::WIFI:
							$this->handleWifi($device,$value,$time);
							break;
						case self::USER:
							$this->handleUser($device,$value,$time);
							break;
						case self::TIMEREBOOT:
							$this->handleReboot($device,$value,$time);
							break;
						case self::CHILD:
							$this->handleChild($device,$toUid,$gid,$value,$time);
							break;
						case self::COMMRESULT:
							$this->handleCommandResult($device,$value,$time);
							break;
						default:
							# code...
							break;
					}
					logger("Handle {$key} data of the device[" . $mac . "] done",DEBUG);
				}
			},function($mysql,$e){
				logger("code:" . $e->getCode() . ";message:" . $e->getMessage(),ERROR);
			});
		}
	}

	//处理系统数据
	private function handleSystem($device,$toUid,$gid,$data,$time,$parentMac = ""){
		$mac = !empty($device) ? $device["dev_mac"] : $data["mac"];
		//如果数据中只有cpu使用率、内存使用率、运行时间，则只需处理缓存,此时设备必须存在
		if(!empty($device) && empty(array_diff_key($data,["cpu_use" => "0","memory_use" => "0","runtime" => "0"]))){
			$rs1 = true;
			$rs2 = true;
		}else{
			$sysArr = [
				"name" => "",
				"chip" => "",
				"dev_ip" => "",
				"net_ip" => "",
				"version" => "",
				"type" => "",
				"mode" => ""
			];
			$deviceData = array_intersect_key($data,$sysArr);
			if(isset($data["location"]["lat"]) && isset($data["location"]["lng"])){
				$deviceData["latitude"] = $data["location"]["lat"];
				$deviceData["longitude"] = $data["location"]["lng"];
			}
			$deviceData["is_ip_location"] = !isset($data["location"]) && isset($data["net_ip"]) ? 1 : 0;
			$deviceData["pid"] = !empty($parentMac) ? $parentMac : "";
			$deviceData["updated_at"] = $time;

			if(!empty($device)){
				if(!empty($device["user_id"])){//设备已绑定用户
					if($toUid != $device["user_id"]){//已绑定设备如果要绑定其他用户,需要先解绑设备
						throw new \Exception("The device is binded to another user",config('exceptions.DEV_BINDED'));
					}
				}else{//设备未绑定用户，执行绑定操作
					$deviceData["user_id"] = $toUid;
					$deviceData["join_time"] = $time;
				}

				$rs1 = $this->deviceModel->save($deviceData,["dev_mac" => $mac],"device");
			}else{
				$deviceData["user_id"] = $toUid;
				$deviceData["dev_mac"] = $mac;
				$deviceData["join_time"] = $time;
				$deviceData["created_at"] = $time;
				$rs1 = $this->deviceModel->add($deviceData,false,"device");
			}

			if(!empty($device[self::SYSTEM])){
				$params = $this->mergeArray($device[self::SYSTEM],$data);
				$rs2 = $this->deviceModel->save([
					"params" => json_encode($params,JSON_UNESCAPED_UNICODE),
					"updated_at" => $time
				],[
					"dev_mac" => $mac,
					"type" => config("device.typeinfo.system")
				],"device_params");
			}else{
				$rs2 = $this->deviceModel->add([
					"dev_mac" => $mac,
					"params" => json_encode($data,JSON_UNESCAPED_UNICODE),
					"type" => config("device.typeinfo.system"),
					"created_at" => $time,
					"updated_at" => $time
				],false,"device_params");
			}
			//拓扑关系处理
			if(isset($data["parent"]["mac"]) && !empty($data["parent"]["mac"])){
				$relationData = [
					"uid" => $toUid,
					"mac" => $mac,
					"pid" => $data["parent"]["mac"]
				];
				$rs3 = $this->deviceModel->add($relationData,true,"device_relation");
			}else{
				$rs3 = true;
			}
		}

		if($rs1 && $rs2 && $rs3){
			//如果没有设置位置信息,则需要ip进行位置定位
			if(!isset($data["location"]) && isset($data["net_ip"])){
				$this->cacheService->setLocation(["mac" => $mac,"net_ip" => $data["net_ip"]]);
			}
			//更新设备CPU使用率、内存使用率、运行时间
			$this->cacheService->parseStatus($mac,$data,$time);
		}else{
			throw new \Exception("Handle the device[" . $mac . "] system data failure",config('exceptions.MYSQL_EXEC_ERROR'));
		}
	}

	//网络数据处理
	public function handleNetwork($device,$data,$time){
		if(!empty($data)){
			if(!empty($device[self::NETWORK])){
				$params = $this->mergeArray($device[self::NETWORK],$data);
				$rs = $this->deviceModel->save([
					"params" => json_encode($params,JSON_UNESCAPED_UNICODE),
					"updated_at" => $time
				],[
					"dev_mac" => $device["dev_mac"],
					"type" => config("device.typeinfo.network")
				],"device_params");
			}else{
				$rs = $this->deviceModel->add([
					"dev_mac" => $device["dev_mac"],
					"params" => json_encode($data,JSON_UNESCAPED_UNICODE),
					"type" => config("device.typeinfo.network"),
					"created_at" => $time,
					"updated_at" => $time
				],false,"device_params");
			}
			
			if(!$rs){
				throw new \Exception("Handle the device[" . $device["dev_mac"] . "] network data failure",config('exceptions.MYSQL_EXEC_ERROR'));
			}
		}
	}

	//处理无线数据
	public function handleWifi($device,$data,$time){
		if(isset($data["total"]) && $data["total"] > 0){
			if(!empty($device[self::WIFI])){
				if(isset($data["radios"]) && !empty($data["radios"])){
					foreach($device[self::WIFI]["radios"] as $k => $radio){
						if(isset($data["radios"][$k]["vap"])){
							foreach ($radio["vap"] as $m => $vap) {
								$vaps[] = isset($data["radios"][$k]["vap"][$m]) ? $this->mergeArray($vap,$data["radios"][$k]["vap"][$m]) : $vap;
							}
							$data["radios"][$k]["vap"] = $vaps;
						}
						$radios[] = isset($data["radios"][$k]) ? $this->mergeArray($radio,$data["radios"][$k]) : $radio;
					}
					$data["radios"] = $radios;
				}
				$params = $this->mergeArray($device[self::WIFI],$data);
				$rs = $this->deviceModel->save([
					"params" => json_encode($params,JSON_UNESCAPED_UNICODE),
					"updated_at" => $time
				],[
					"dev_mac" => $device["dev_mac"],
					"type" => config("device.typeinfo.wifi")
				],"device_params");
			}else{
				$rs = $this->deviceModel->add([
					"dev_mac" => $device["dev_mac"],
					"params" => json_encode($data,JSON_UNESCAPED_UNICODE),
					"type" => config("device.typeinfo.wifi"),
					"created_at" => $time,
					"updated_at" => $time
				],false,"device_params");
			}
			
			if(!$rs){
				throw new \Exception("Handle the device[" . $device["dev_mac"] . "] wifi data failure",config('exceptions.MYSQL_EXEC_ERROR'));
			}
		}
	}

	//处理用户数据
	public function handleUser($device,$data,$time){
		if(isset($data["total"])){
			if(!empty($device[self::USER])){
				$params = $this->mergeArray($device[self::USER],$data);
				$rs1 = $this->deviceModel->save([
					"params" => json_encode($params,JSON_UNESCAPED_UNICODE),
					"updated_at" => $time
				],[
					"dev_mac" => $device["dev_mac"],
					"type" => config("device.typeinfo.user")
				],"device_params");
			}else{
				$rs1 = $this->deviceModel->add([
					"dev_mac" => $device["dev_mac"],
					"params" => json_encode($data,JSON_UNESCAPED_UNICODE),
					"type" => config("device.typeinfo.user"),
					"created_at" => $time,
					"updated_at" => $time
				],false,"device_params");
			}
			
			$rs2 = $this->deviceModel->add([
				"mac" => $device["dev_mac"],
				"onlines" => $data["total"],
				"created_at" => $time,
				"updated_at" => $time
			],false,"device_clients_nums");
			if(!$rs1 || !$rs2){
				throw new \Exception("Handle the device[" . $device["dev_mac"] . "] user data failure",config('exceptions.MYSQL_EXEC_ERROR'));
			}
		}
	}

	//定时重启数据
	public function handleReboot($device,$data,$time){
		if(!empty($data)){
			if(!empty($device[self::TIMEREBOOT])){
				$params = $this->mergeArray($device[self::TIMEREBOOT],$data);
				$rs = $this->deviceModel->save([
					"params" => json_encode($params,JSON_UNESCAPED_UNICODE),
					"updated_at" => $time
				],[
					"dev_mac" => $device["dev_mac"],
					"type" => config("device.typeinfo.time_reboot")
				],"device_params");
			}else{
				$rs = $this->deviceModel->add([
					"dev_mac" => $device["dev_mac"],
					"params" => json_encode($data,JSON_UNESCAPED_UNICODE),
					"type" => config("device.typeinfo.time_reboot"),
					"created_at" => $time,
					"updated_at" => $time
				],false,"device_params");
			}
			
			if(!$rs){
				throw new \Exception("Handle the device[" . $device["dev_mac"] . "] reboot_time data failure",config("exceptions.MYSQL_EXEC_ERROR"));
			}
		}
	}

	//子设备数据
	public function handleChild($pdevice,$toUid,$gid,$data,$time){
		if(isset($data["list"]) && !empty($data["list"])){//全量上报
			logger("Start handle all childs of the device[" . $pdevice["dev_mac"] . "]",DEBUG);
			if(count($data["list"]) > config("public.up_number")){
				throw new \Exception("Too many sub devices",config('exceptions.YUNLOT_UPINFO_ERROR'));
			}
			if($data["index"] == 0){//首页信息，则需要先清除所有子设备信息
				logger("First to clear the child of the device[" . $pdevice["dev_mac"] . "]",DEBUG);
				$childs = $this->deviceModel->getInfos(["pid" => $pdevice["dev_mac"]],"device");
				if(!empty($childs)){
					$childMacs = array_column($childs,"dev_mac");
					$rs1 = $this->deviceModel->delete(["pid" => $pdevice["dev_mac"]],"device");
					$rs2 = $this->deviceModel->delete(["dev_mac" => ["IN",$childMacs]],"device_params");
					if(!$rs1 || !$rs2){
						throw new \Exception("Clear child device failure",config("exceptions.MYSQL_EXEC_ERROR"));
					}
				}
			}
			$this->handChildData($data["list"],$toUid,$gid,$time,$pdevice);
			logger("Handle all childs of the device[" . $pdevice["dev_mac"] . "] done",DEBUG);
		}else{//增量上报
			logger("Start handle increment childs of the device[" . $pdevice["dev_mac"] . "]",DEBUG);
			if(isset($data["change"]) && !empty($data["change"])){
				$this->handChildData($data["change"],$toUid,$gid,$time,$pdevice);
			}
			if(isset($data["delete"]) && !empty($data["delete"])){
				$rs1 = $this->deviceModel->delete(["dev_mac" => ["IN",$data["delete"]]],"device");
				$rs2 = $this->deviceModel->delete(["dev_mac" => ["IN",$data["delete"]]],"device_params");
				if(!$rs1 || !$rs2){
					throw new \Exception("Clear child device failure",config("exceptions.MYSQL_EXEC_ERROR"));
				}
			}
			logger("Handle increment childs of the device[" . $pdevice["dev_mac"] . "] done",DEBUG);
		}
	}

	private function handChildData($data,$toUid,$gid,$time,$pdevice){
		foreach($data as $value){
			$mac = $value["mac"];
			unset($value["mac"]);
			$device = $this->deviceModel->getDevices($mac);
			$_device = !empty($device) ? $device : ["dev_mac" => $mac];
			foreach ($value as $k => $v) {
				logger("Start handle {$k} data of the device[" . $mac . "]",DEBUG);
				switch ($k) {
					case self::SYSTEM:
						$this->handleSystem($device,$toUid,$gid,$v,$time,$pdevice["dev_mac"]);
						break;
					case self::NETWORK:
						$this->handleNetwork($_device,$v,$time);
						break;
					case self::WIFI:
						$this->handleWifi($_device,$v,$time);
						break;
					case self::USER:
						$this->handleUser($_device,$v,$time);
						break;
					case self::TIMEREBOOT:
						$this->handleReboot($_device,$v,$time);
						break;
					default:
						# code...
						break;
				}
				logger("Handle {$k} data of the device[" . $mac . "] done",DEBUG);
			}			
		}
	}

	//处理命令执行结果
	public function handleCommandResult($device,$data,$time){
		$this->deviceModel->save([
			"status" => $data["status"],
			"updated_at" => $time
		],[
			"dev_mac" => $device["dev_mac"],
			"comm_id" => $data["commid"]
		],"command");
	}

    private function mergeArray($base,$data){
    	return array_merge($base,array_intersect_key($data,$base));
    }

    /**
     * 过滤不需要处理数据,1、未注册的产品，数据不处理;2、未绑定用户的产品仅处理系统信息;3、未发布成功的产品仅处理绑定开发者账号
     *
     * @param  [type] $prtid [description]
     * @param  [type] $cltid [description]
     * @param  [type] $mac   [description]
     * @param  [type] $bind [description]
     * @param [type] $toUid
     * @return [type]        [description]
     */
    private function checkData($prtid,$cltid,$mac,$bind,$toUid){
    	if(!$registerInfo = $this->cacheService->getRegister($prtid,$mac)){
    		$registerInfo = $this->deviceModel->getRegister($prtid,$mac);
    		$this->cacheService->setRegister($prtid,$mac,$registerInfo,config("public.cache.registerttl"));
    	}

    	if(empty($registerInfo)){//未注册产品，不处理
    		throw new \Exception("The product[$prtid] is not exists",config("exceptions.PRT_NO"));
    	}else{
    		if($registerInfo["bind"] != $bind){
    			throw new \Exception("The bind code is error",config("exceptions.BINDCODE_ERROR"));
    		}
    		if($registerInfo["aud_status"] != 4 && $registerInfo["developUid"] != $toUid){//未发布产品只能绑定到开发者账号下
    			throw new \Exception("The product[$prtid] unpublished,just only bind to the develop",config("exceptions.PRT_STATUS_NO_ALLOW"));
    		}
    		if(empty($registerInfo["cltid"])){//设备未连接过云平台，不能进行绑定
    			throw new \Exception("The device of the product is not connect to cloudnetlot",config("exceptions.DEV_NO_CONNECT"));
    		}
    		if(!empty($registerInfo["bindUid"]) && $registerInfo["bindUid"] != $toUid){//上报信息与绑定用户不符合
    			throw new \Exception("The device is binded to another user",config("exceptions.DEV_BINDED"));
    		}
    	}
    }
}