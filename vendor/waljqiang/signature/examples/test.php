<?php
require_once __DIR__ . "/../vendor/autoload.php";
use Waljqiang\Signature\Signature;

$a = Signature::encrypto('123');
$b = Signature::decrypto($a);

var_dump($a);
var_dump($b);