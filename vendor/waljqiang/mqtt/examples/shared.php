<?php
require_once __DIR__ . "/../vendor/autoload.php";

$config = [
	'address' => 'tcp://192.168.33.10:1883',
	'username' => 'admin',
	'password' => '726215',
	'clean' => true,
	'qos' => 0,
	'retain' => 0,
	'keepalive' => 10,
	'timeout' => 60
];