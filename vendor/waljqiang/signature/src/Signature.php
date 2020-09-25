<?php

namespace Waljqiang\Signature;

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;

class Signature{
    const SIGNATUER_KEY = 'def0000006f5a5d91ff06f23e3610c263881ae3d815c37cc98104eacf2fab081aa82e86559d11fcd76c3434aaeed80ecc61abac759f47237a18630b66f393289518cfa60';

    public function __construct(){
        //导入类库
    }

    /**
     * @return Key
     * @throws EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\BadFormatException
     */
    private static function loadEncryptionKeyFromConfig(){
        $keyAscii = self::SIGNATUER_KEY;
        return Key::loadFromAsciiSafeString($keyAscii);
    }

    /**
     * 对称加密
     * @param $data string 文档
     * @return string 加密密文
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\BadFormatException
     */
    public static function encrypto($data){
        $key = self::loadEncryptionKeyFromConfig();
        return Crypto::encrypt($data, $key);
    }

    /**
     * 对称解密
     * @param $cipherText string 密文
     * @return string 明文
     */
    public static function decrypto($cipherText){
        try {
            $key = self::loadEncryptionKeyFromConfig();
            $secret_data = Crypto::decrypt($cipherText, $key);
            return $secret_data;
        } catch (EnvironmentIsBrokenException $e) {
            return false;
        } catch (WrongKeyOrModifiedCiphertextException $e) {
            return false;
        } catch (BadFormatException $e) {
            return false;
        }
    }
}