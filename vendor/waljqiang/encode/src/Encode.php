<?php
namespace Waljqiang\Encode;

class Encode{
	const AES = "AES";
	public $nonceLength = 6;
	public $ishex = true;

	private $token;
	private $nonce;
	private $timeStamp;

	private $signature;

	private $encode;

	public function __construct(){

	}

	public function init($token,$type="",$encodeConfig=[]){
		$this->setToken($token);
		$this->setNonce();
		$this->setTimeStamp();
		if(!empty($type)){
			$className = "\\Encode\\" . $type;
			$this->encode = new $className($encodeConfig);
		}
	}

	public function setToken($token){
		$this->token = $token;
	}

	public function setTimeStamp($timeStamp = ""){
		$this->timeStamp = empty($timeStamp) ? time() : $timeStamp;
	}

	public function setNonce($nonce = ""){
		$this->nonce = empty($nonce) ? $this->getRandomStr($this->nonceLength) : $nonce;
	}

	public function setSignature($signature){
		$this->signature = $signature;
	}

	public function checkSignature($encrypted){
		return $this->signature == SHA1::getSHA1($this->token, $this->timeStamp, $this->nonce,$encrypted);
	}

	public function encode($data){
		if(!empty($this->encode)){
			$encrypted = $this->encode->encrypt($data);
			//生成安全签名
			$signature = SHA1::getSHA1($this->token, $this->timeStamp, $this->nonce,$encrypted);
			return [
				"signature" => $signature,
				"timestamp" => $this->timeStamp,
				"nonce" => $this->nonce,
				"encrypted" => $this->ishex ? bin2hex($encrypted) : $encrypted
			];
		}else{
			return $data;
		}
	}

	public function decode($data){
		if(!empty($this->encode)){
			$encrypted = $this->ishex ? hex2bin($data) : $data;
			//验证安全签名
			if(!$this->checkSignature($encrypted)){
				throw new \Exception("Signature error", -1);	
			}
			return $this->encode->decrypt($encrypted);
		}else{
			return $data;
		}
	}

	public function getRandomStr($length = 16){
		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($str_pol) - 1;
		for ($i = 0; $i < $length; $i++) {
			$str .= $str_pol[mt_rand(0, $max)];
		}
		return $str;
	}

}