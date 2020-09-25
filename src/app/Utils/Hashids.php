<?php
namespace App\Utils;

use Hashids\Hashids as Base;

class Hashids extends Base{
    private $enable;
	protected $prefix;

	public function __construct($salt = '', $minHashLength = 0, $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',$prefix = '',$enable = true){
		$this->prefix = $prefix;
        $this->enable = $enable;
		parent::__construct($salt,$minHashLength,$alphabet);
	}

    public function encodeHash(...$numbers){
        if($this->enable){
            $ret = parent::encode($numbers);
            return $this->prefix . $ret;
        }else{
            return $numbers[0];
        }
    }

    public function decodeHash($hash){
        if($this->enable){
            $hash = str_replace($this->prefix, '', $hash);
            return parent::decode($hash);
        }else{
            return $hash;
        }
    }

    public function setPrefix($prefix){
        $this->prefix = $prefix;
    }

}
