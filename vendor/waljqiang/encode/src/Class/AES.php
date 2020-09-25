<?php
namespace Encode;

use Waljqiang\Encode\Encode;
use Encode\Aes\PKCS7Encode;

class AES implements EncodeInterface{
	private $key;

	public function __construct($config){
		$this->key = base64_decode($config["key"] . "=");
	}

	public function encrypt($text){
		try{
			//获得16位随机字符串，填充到明文之前
			$random = $this->getRandomStr();
			$text = $random . pack("N", strlen($text)) . $text;
			//使用自定义的填充方式对明文进行补位填充
			$pkc_encoder = new PKCS7Encode;
			$text = $pkc_encoder->encode($text);
			// 网络字节序
			/*$size = @mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$module = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($this->key, 0, 16);
			@mcrypt_generic_init($module, $this->key, $iv);
			//加密
			$encrypted = mcrypt_generic($module, $text);
			@mcrypt_generic_deinit($module);
			@mcrypt_module_close($module);*/
			$encrypted = openssl_encrypt($text, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA,substr($this->key, 0, 16));
			return base64_encode($encrypted);
		}catch(\Exception $e){
			throw new Exception("AES encode failure", -1);	
		}
	}

	public function decrypt($data){
		try {
			//使用BASE64对需要解密的字符串进行解码
			$ciphertext_dec = base64_decode($data);
			/*$module = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($this->key, 0, 16);
			@mcrypt_generic_init($module, $this->key, $iv);

			//解密
			$decrypted = @mdecrypt_generic($module, $ciphertext_dec);
			@mcrypt_generic_deinit($module);
			@mcrypt_module_close($module);*/
			$decrypted = openssl_decrypt($ciphertext_dec, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA,substr($this->key, 0, 16));
		} catch (Exception $e) {
			throw new \Exception("AES decrypt failure", -1);
		}

		try {
			//去除补位字符
			$pkc_encoder = new PKCS7Encode;
			$result = $pkc_encoder->decode($decrypted);
			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16)
				return "";
			$content = substr($result, 16, strlen($result));
			$len_list = unpack("N", substr($content, 0, 4));
			$str_len = $len_list[1];
			$res = substr($content, 4, $str_len);
		} catch (Exception $e) {
			throw new \Exception("AES decrypt error IllegalBuffer", -1);
		}
		return $res;
	}

	private function getRandomStr($length = 16){
		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($str_pol) - 1;
		for ($i = 0; $i < $length; $i++) {
			$str .= $str_pol[mt_rand(0, $max)];
		}
		return $str;
	}

}