<?php
namespace app\Services;

use app\Models\TestModel;
use app\Models\DeviceModel;

class TestService extends BaseService{
	protected $testModel;
	protected $deviceModel;

	public function initialization(&$context){
		parent::initialization($context);
		$this->testModel = $this->loader->model(TestModel::class,$this);
		$this->deviceModel = $this->loader->model(DeviceModel::class,$this);
	}

	public function testRedis(){
		return $this->testModel->testRedis();
	}

	public function testMysql(){
		$mac = "44:D1:FA:08:B9:F5";
		$data = [
			"name" => "123",
			"version" => "123",
			"chip" => "123"
		];
		for($i=0;$i<100;$i++){
			$rs = 0;
			$time = $i;
			$this->db->begin(function() use (&$rs,$mac,$data,$time){
				if($info = $this->deviceModel->getInfo(["dev_mac" => $mac],"device")){//已激活,更新数据
					$rs1 = $this->deviceModel->save([
						"dev_mac" => $mac,
						"name" => $data["name"],
						"version" => $data["version"],
						"latitude" => isset($data["location"]) ? $data["location"]["lat"] : "",
						"longitude" => isset($data["location"]) ? $data["location"]["lng"] : "",
						"chip" => $data["chip"],
						"is_ip_location" => !isset($data["location"]) ? 0 : 1,
						"updated_at" => $time
					],["dev_mac" => $mac],"device");
					$rs2 = $this->deviceModel->save([
						"params" => json_encode($data,JSON_UNESCAPED_UNICODE),
						"updated_at" => $time
					],[
						"dev_mac" => $mac,
						"type" => config('yunlot.upinfotype.active')
					],"device_params");
				}else{//未激活，直接插入数据
					$rs1 = $this->deviceModel->add([
						"dev_mac" => $mac,
						"name" => $data["name"],
						"version" => $data["version"],
						"latitude" => isset($data["location"]) ? $data["location"]["lat"] : "",
						"longitude" => isset($data["location"]) ? $data["location"]["lng"] : "",
						"chip" => $data["chip"],
						"is_ip_location" => !isset($data["location"]) ? 0 : 1,
						"created_at" => $time,
						"updated_at" => $time
					],false,"device");
					$rs2 = $this->deviceModel->add([
						"dev_mac" => $mac,
						"params" => json_encode($data,JSON_UNESCAPED_UNICODE),
						"type" => config('yunlot.upinfotype.active'),
						"created_at" => $time,
						"updated_at" => $time
					],false,"device_params");
				}
				if($rs1 && $rs2){
					$rs = 1;
				}else{
					$rs = 2;
				}
			});
			echo $rs;
		}
	}

	public function upinfo(){
		$mac = uniqid();
		for($i=1;$i<10000;$i++){
			$data = '{"header":{"protocol":"v1.0","type":"1","encode":{"type":"2","token":"cloudnetlot","key":"abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG","nonce":"abcdef","timestamp":"1587440903","signature":"680dfe6dd7fdf491c187d5f178dd675030e9166d"}},"body":"626261456f45674e595837315a484155474c3832776138367179666f71544d397956504e617a67596b4d5259596136485371515177523869695050526234693753692b4435474c466d36317845574939414d4530775a524e4f674751553331526548736959456b4b595757795051516b696c2f37496f703539432b625052313135563558324c6c456234446f7a34736f666f586a7a354b39323072685474653545346a41763748596e63534f56615168375666537155414f6c62334e76325763444470365549794a636a323643394853393331696e6c5a4d45554b6d6c436542336a5a5a4a4f4d6661526b6141366570704b656b4e57352b433963314e535a4b63507a7967374230422f4244376a7339533148752f6e4d74317a3132446c41436f47773972747479354d4a33457774466852474451723657537270753965626267743338396a656e4d644f634b4d747a355179625858586458445a396a706e715153726b3541535773646f3d","now":"1587440903"}';
			$this->redis->lpush(config('redis.prefix') . 'device:queue:data',json_encode(["mac" => $mac,"data" => $data],JSON_UNESCAPED_UNICODE));
		}
	}

}