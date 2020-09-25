<?php
namespace Waljqiang\Yunlot\Profile;

use Waljqiang\Encode\Encode;
use Waljqiang\Yunlot\Exception\YunlotException;

class YunlotVersionv10 extends YunlotVersion{
	const VERSION = "v1.0";

	private $encodeType = [
		"1" => "",
		"2" => "AES"
	];

	private $header;
	private $body;
	private $now;

	/**
	 * 加解密类
	 * @var [type]
	 */
	private static $encode;

	public function __construct($config){
		$encode = new Encode();
		if(!in_array($config["encodetype"],["1","2"])){
			throw new YunlotException("The protocol of v1.0 only supports plaintext and AES encryption",YunlotException::YUNLOT10_HEADER_ENCODE_ERROR);
		}
		$encodeType = $this->encodeType[$config["encodetype"]];
		$key = $config["encodetype"] == 1 ? [] : ["key" => $config["key"]];
		$encode->init($config["token"],$encodeType,$key);
		self::$encode = $encode;
		$this->header = [
			"protocol" => self::VERSION,
			"type" => "",
			"encode" => $config["encodetype"],
			"mid" => $this->getRandomStr(6) . time()
		];
	}

	public function init(){
		$this->header = array_intersect_key(["protocol" => self::VERSION,"type" => "","encode" => $this->header["encode"],"mid" => $this->getRandomStr(6) . time()],$this->header);
		return $this;
	}

	public function getVersion(){
		return self::VERSION;
	}

	public function setHeader($headers = []){
		$this->header = array_merge($this->header,$headers);
		if($this->header["encode"] == 1){
			unset($this->header["nonce"]);
			unset($this->header["timestamp"]);
		}
		$this->checkHeader();
		return $this;
	}

	public function getHeader($key = "",$default = NULL){
		return !empty($key) ? $this->array_get($this->header,$key,$default) : $this->header;
	}

	public function setBody($body){
		try{
			switch ($this->getHeader("encode")) {
				case 1:
					$this->body = $body;
					break;
				case 2:
					$data = $this->encrypt(json_encode($body,JSON_UNESCAPED_UNICODE));
					$this->setHeader([
						"nonce" => $data["nonce"],
						"timestamp" => $data["timestamp"],
						"signature" => $data["signature"]
					]);
					$this->body = $data["encrypted"];
					break;
				default:
					$this->body = $body;
					break;
			}
			return $this;
		}catch(\Exception $e){
			throw new YunlotException("Set body with yunlot v1.0 is failure",YunlotException::YUNLOT10_BODY_SET_ERROR);		
		}
	}

	public function getBody($key = "",$default = NULL){
		return !empty($key) ? $this->array_get($this->body,$key,$default) : $this->body;
	}

	public function setNonce($nonce = ""){
		$nonce = !empty($nonce) ? $nonce : $this->getRandomStr(6);
		$this->setHeader(["nonce" => $nonce]);
		return $this;
	}

	public function setTimestamp($timestamp = ""){
		$timestamp = !empty($timestamp) ? $timestamp : time();
		$this->setHeader(["timestamp" => $timestamp]);
		return $this;
	}

	public function setNow($now = ""){
		$this->now = !empty($now) ? $now : time();
		return $this;
	}

	public function getNow(){
		return $this->now;
	}

	public function parse($str){
		$data = json_decode($str,true);
		$this->checkData($data);
		$this->setHeader($data["header"]);
		try{
			switch ($this->getHeader("encode","1")) {
				case 1:
					$this->body = $data["body"];
					break;
				case 2:
					$this->body = $this->decrypt($data["body"]);
					break;
				default:
					$this->body = $data["body"];
					break;
			}
			$this->now = $data["now"];
			return $this;
		}catch(\Exception $e){
			throw new YunlotException($e->getMessage(),$e->getCode());
		}
	}

	private function encrypt($string){
		$nonce = !empty($this->getHeader("nonce")) ? $this->getHeader("nonce") : $this->getRandomStr(6);
		$timestamp = !empty($this->getHeader("timestamp")) ? $this->getHeader("timestamp") : time();
		self::$encode->setNonce($nonce);
		self::$encode->setTimeStamp($timestamp);
		return self::$encode->encode($string);
	}

	private function decrypt($data){
		try{
			self::$encode->setNonce($this->getHeader("nonce"));
			self::$encode->setTimeStamp($this->getHeader("timestamp"));
			self::$encode->setSignature($this->getHeader("signature"));
			$result = json_decode(self::$encode->decode($data),true);
			if(empty($result)){
				throw new YunlotException("The data of yunlot v1.0 decrypt failure",YunlotException::YUNLOT10_DECODE_ERROR);
			}
			return $result;
		}catch(\Exception $e){
			throw new YunlotException("The data of yunlot v1.0 decrypt failure",YunlotException::YUNLOT10_DECODE_ERROR);
		}
	}

	public function out(){
		return json_encode([
			"header" => $this->header,
			"body" => $this->body,
			"now" => $this->now
		],JSON_UNESCAPED_UNICODE);
	}

	protected function checkData($data){
		if(!isset($data["header"]) || !isset($data["body"]) || !isset($data["now"])){
			throw new YunlotException("The yunlot v1.0 must be include header、body and now",YunlotException::YUNLOT10_FORMAT_ERROR);
		}
	}

	protected function checkHeader(){
		if(!isset($this->header["protocol"]) || !isset($this->header["type"]) || !isset($this->header["encode"]) || !isset($this->header["mid"])){
			throw new YunlotException("The header of yunlot v1.0 must be protocol 、type、encode and mid",YunlotException::YUNLOT10_HEADER_FORMAT_ERROR);
		}
		if($this->getHeader("protocol") == $this->getVersion()){
			if(!in_array($this->getHeader("type"),[1,2])){
				throw new YunlotException("The type of the header in yunlot v1.0 must be '1' or '2'",YunlotException::YUNLOT10_HEADER_TYPE_ERROR);
			}
			if(!in_array($this->getHeader("encode"),[1,2])){
				throw new YunlotException("The encode of the header in yunlot v1.0 must be '1' or '2'",YunlotException::YUNLOT10_HEADER_TYPE_ERROR);
			}
		}
	}
}