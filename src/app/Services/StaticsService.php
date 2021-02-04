<?php
namespace app\Services;

use app\Models\DeviceModel;
use Carbon\Carbon;

class StaticsService extends BaseService{
	protected $deviceModel;

	public function initialization(&$context){
		parent::initialization($context);
		$this->deviceModel = $this->loader->model(DeviceModel::class,$this);
	}

	public function ClientsForDeviceByHours($datas){
		try{
			if(!empty($datas)){
				$ids = array_column($datas,"id");
				$keyDatas = [];
				$resultDatas = [];
				$i = 0;
				foreach ($datas as $data) {
					$time = Carbon::now()->timestamp;
					$hours = Carbon::createFromFormat("Y-m-d H:i:s",date('Y-m-d H:i:s',$data["created_at"]))->addHours(1)->startOfHour()->toDateTimeString();
					if(isset($keyDatas[$data["mac"]][$hours])){
						$resultDatas[$keyDatas[$data["mac"]][$hours]]["onlines"] = $data["onlines"];
					}else{
						$resultDatas[$i] = [
							"mac" => $data["mac"],
							"onlines" => $data["onlines"],
							"hours" => $hours,
							"created_at" => $time,
							"updated_at" => $time
						];
						$keyDatas[$data["mac"]][$hours] = $i;
					}
					$i = $i + 1;
				}

				$this->db->begin(function()use($ids,$resultDatas){
					$rs1 = $this->deviceModel->addAll($resultDatas,"device_clients_statics_hour",true);
					$rs2 = $this->deviceModel->saveStaticsStatus($ids,4);
				},function($mysql,$e){
					$rs = $this->deviceModel->saveStaticsStatus($ids,3);
				});
			}
		}catch(\Exception $e){
			logger("ClientsForDeviceByHours error " . $e->getMessage(),ERROR);
		}
	}

}