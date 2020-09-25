<?php
namespace Waljqiang\Yunlot\Profile;

use Waljqiang\Encode\Encode;
use Waljqiang\Yunlot\Exception\YunlotException;

abstract class YunlotVersion implements ProfileInterface{

	public function __construct(){

	}

	abstract public function getVersion();

	abstract public function getHeader($key = "",$default = NULL);

	abstract public function getBody($key = "",$default = NULL);

	abstract public function setHeader($header);

	abstract public function setBody($body);

	abstract public function setNow($now);

	public function getRandomStr($length = 16){
		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($str_pol) - 1;
		for ($i = 0; $i < $length; $i++) {
			$str .= $str_pol[mt_rand(0, $max)];
		}
		return $str;
	}

	public function array_get($array,$key,$default = NULL){
		foreach (explode(".",$key) as $segment) {
			if(isset($array[$segment]))
				$array = $array[$segment];
			else
				return $default;
		}
		return $array;
	}
}