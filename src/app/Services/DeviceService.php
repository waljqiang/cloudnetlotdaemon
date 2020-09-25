<?php
namespace app\Services;

use app\Models\DeviceModel;
use Carbon\Carbon;

class DeviceService extends BaseService{
	protected $cacheService;
	protected $deviceModel;
	protected $getIPAddress;

	public function initialization(&$context){
		parent::initialization($context);
		$this->cacheService = $this->loader->model(CacheService::class,$this);
		$this->deviceModel = $this->loader->model(DeviceModel::class,$this);
		$this->getIPAddress = get_instance()->getAsynPool('GetIPAddress');
	}

	public function locationWithIP($data){
		try{
			$locationInfo = $this->getIPlocation($data['net_ip']);
			$area = $this->deviceModel->getInfo(['name' => $locationInfo['city']],'area',['code']);
			if(!empty($locationInfo)){
				$rs = $this->deviceModel->save([
					'area' => isset($area['code']) && !empty($area['code']) ? $area['code'] : 0,
					'country' => $locationInfo['country'],
					'province' => $locationInfo['province'],
					'city' => $locationInfo['city'],
					'address' => $locationInfo['address'],
					'longitude' => $locationInfo['longitude'],
					'latitude' => $locationInfo['latitude'],
					'is_ip_location' => 0,
					'updated_at' => Carbon::now()->timestamp
				],[
					'dev_mac' => $data['mac']
				],'device');
				if(!$rs){
					logger('location device[' . $data['mac'] . '] with IP[' . $data['net_ip'] . '] failure',ERROR);
					$this->cacheService->setLocation($data);
				}else{
					logger('location device[' . $data['mac'] . '] success');
				}
			}else{
				$this->cacheService->setLocation($data['mac']);
			}
		}catch(\Exception $e){
			logger('location device[' . $data['mac'] . '] with IP[' . $device['net_ip'] . '] remote failure',ERROR);
			$this->cacheService->setLocation($data['mac']);
		}
	}

	private function getIPlocation($clientIP){
		$res = '';
		try{
			$url = sprintf(config('public.iplocation.baiduapi'),$clientIP);
			$locationInfo = $this->getIPAddress->httpClient->request('GET',$url)->getBody();
			$locationInfo = @json_decode($locationInfo,true);
			if(isset($locationInfo['status']) && $locationInfo['status'] == 0){
				$res = [
					'country' => config('public.iplocation.china'),
					'province' => $locationInfo['content']['address_detail']['province'],
					'city' => $locationInfo['content']['address_detail']['city'],
					'address' => $locationInfo['content']['address_detail']['district'] . $locationInfo['content']['address_detail']['street'] . $locationInfo['content']['address_detail']['street_number'],
					'longitude' => $locationInfo['content']['point']['x'],
					'latitude' => $locationInfo['content']['point']['y']
				];
			}else{
				$url = sprintf(config('public.iplocation.ipapi'),$clientIP,'zh-CN');
				$locationInfo = $this->getIPAddress->request('GET',$url)->getBody();
				$locationInfo = @json_decode($locationInfo,true);
				if(isset($locationInfo['status']) && strtolower($locationInfo['status']) == 'success'){
					$res = [
						'country' => $locationInfo['country'],
						'province' => $locationInfo['regionName'],
						'city' => $locationInfo['city'],
						'address' => $locationInfo['district'],
						'longitude' => $locationInfo['lon'],
						'latitude' => $locationInfo['lat']
					];
				}else{
					logger('location device[' . $devMac . '] with IP[' . $clientIP . '] remote parse failure',ERROR);
				}
			}
		}catch(\Exception $e){
			logger('location device[' . $devMac . '] with IP[' . $clientIP . '] remote failure',ERROR);
		}
		return $res;
	}

}