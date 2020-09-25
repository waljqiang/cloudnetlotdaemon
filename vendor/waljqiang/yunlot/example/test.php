<?php
require_once __DIR__ . "/../vendor/autoload.php";
use Waljqiang\Yunlot\Yunlot;
try{
	$token = "cloudnetlot";
	$encodetype = "2";
	$key = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
	$nonce = "abcdef";
	$timestamp = "1587440903";
	$yunlot = new Yunlot(["protocol" => "v1.0","encodetype" => $encodetype,"token" => $token,"key" => $key]);
	$yunlot->init()->setHeader(["type" => "1","bind" => "1sadfasdfdsdsfdsdasf"]);
	$yunlot->setBody([
		"system" => [
			"name" => "CPE120M",
			"chip" => "RTL8197F-FR350",
			"cpu" => "1GHz",
			"flash" => "8",
			"ram" => "64",
			"mac" => "44:D1:FA:7B:FB:38",
			"dev_ip" => "192.168.1.4",
			"net_ip" => "219.145.1.187",
			"version" => "CPE120M-CPE-V2.0-Build20190927134132",
			"cpu_use" => "20",
			"memory_use" => "43",
			"type" => "CPE120",
			"mode" => "1",
			"ability" => [
				"00010001",
				"00020002"
			],
			"location" => [
				"lat" => "36.60332",
				"lng" => "109.50051"
			],
			"eth" => [
				[
					"id" => "br0",
					"mac" => "46:D1:FA:7B:FB:38"
				],
				[
					"id" => "apcli0",
					"mac" => "44:D1:FA:78:FB:38"
				],
				[
					"id" => "ra0",
					"mac" => "44:D1:FA:7B:FB:38"
				],
				[
					"id" => "ra1",
					"mac" => "46:D1:FA:5B:FB:38"
				],
				[
					"id" => "ra2",
					"mac" => "46:D1:FA:6B:FB:38"
				],
				[
					"id" => "ra3",
					"mac" => "46:D1:FA:7B:FB:38"
				],
				[
					"id" => "ra4",
					"mac" => "46:D1:FA:7B:FB:38"
				]
			],
			"runtime" => "3600"
		]
	]);
	$yunlot->setNow($timestamp);
	$a = $yunlot->out();
	echo "<pre>";
	print_r($a);
	echo "</pre>";

	echo "----------------------------------------------";
	$yunlot->init();
	$b = $yunlot->parse($a);
	echo "<pre>";
	print_r($b);
	echo "</pre>";
}catch(\Exception $e){
	var_dump($e);
}