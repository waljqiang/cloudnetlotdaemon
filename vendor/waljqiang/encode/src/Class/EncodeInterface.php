<?php
namespace Encode;
interface EncodeInterface{
	public function encrypt($data);
	public function decrypt($data);
}