<?php
namespace app\Models;

use app\Models\BaseModel;
use Carbon\Carbon;

class DeviceModel extends BaseModel{

	//获取注册数据
	public function getRegister($prtid,$mac){
		try{
			$sql = "SELECT m.`uid` AS developUid,m.`prtid`,m.`type`,m.`size`,m.`aud_status`,n.`cltid`,n.`mac`,p.`user_id` AS bindUid,p.`bind` FROM " . $this->getTable("develop_product") . " m LEFT JOIN " . $this->getTable("develop_client") . " n ON m.`prtid`=n.`prtid` AND n.`mac`='{$mac}' LEFT JOIN " . $this->getTable("device") . " p ON n.`mac`=p.`dev_mac` WHERE m.`prtid`='{$prtid}'";
			return $this->db->query($sql)->row();
		}catch(\Exception $e){
			logger("sql exception : " . $e->getMessage(),ERROR);
			return [];
		}
	}

	public function getDevices($mac,$type = ["1","2","3","4","5","6","7","8"]){
		try{
			$res = [];
			$sql = "SELECT a.`id`,a.`user_id`,a.`dev_mac`,a.`dev_ip`,a.`net_ip`,a.`name`,a.`prt_type`,a.`prt_size`,a.`type`,a.`mode`,a.`version`,a.`up_time`,a.`pid`,a.`area`,a.`country`,a.`province`,a.`city`,a.`address`,a.`latitude`,a.`longitude`,a.`chip`,a.`sn`,a.`notes`,a.`group_id`,a.`is_ip_location`,a.`is_del`,a.`join_time`,a.`created_at`,a.`updated_at`,b.`type` AS ptype,b.`params` FROM " . $this->getTable("device") . " a LEFT JOIN " . $this->getTable("device_params") . " b ON a.`dev_mac` = b.`dev_mac` AND b.`type` IN ('" . implode("','",$type) . "') WHERE a.`dev_mac` = '{$mac}'";
			$datas = $this->db->query($sql)->getResult();
			$paramsMap = array_flip(config("device.typeinfo"));
			if(!empty($datas["result"])){
				$params = [];
				foreach($datas["result"] as $data){
					if(in_array($data["ptype"],array_keys($paramsMap)))
						$params[$paramsMap[$data["ptype"]]] = json_decode($data["params"],true);
				}
				$res = array_merge($datas["result"][0],[
					"encode" => [],
					"system" => [],
					"network" => [],
					"wifi" => [],
					"user" => [],
					"time_reboot" => []
				],$params);
				unset($res["ptype"]);
				unset($res["params"]);
			}
			return $res;
	    }catch(\Exception $e){
	    	logger("sql exception : " . $e->getMessage(),ERROR);
	    	throw new \Exception("sql exception : " . $e->getMessage(),ERROR);
	    }
	}

	public function saveStaticsStatus($ids,$status=4){
		$sql = "UPDATE " . $this->getTable("device_clients_nums") . " SET `status`=" . $status . ",`retry`=`retry`+1 WHERE `id` IN('" . implode("','",$ids) . "')";
		$res = $this->db->query($sql)->getResult();
		return $res["result"];
	}
}