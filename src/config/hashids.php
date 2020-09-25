<?php
return [
	"hashids" => [
	    "enable" => true,
	    "id" => [
	    	"salt" => "cloudnetlot",
		    "length" => 25,
		    "header" => "cnl",
		    "alphabet" => "abcdefghijklmnopqrstuvwxyz"
	    ],
	    "prt" => [
	    	"salt" => "product",
	    	"length" => 22,
	    	"header" => "prt",
	    	"alphabet" => "abcdefghijklmnopqrstuvwxyz"
	    ],
	    "clt" => [
	    	"salt" => "client",
	    	"length" => 27,
	    	"header" => "clt",
	    	"alphabet" => "abcdefghijklmnopqrstuvwxyz0123456789"
	    ]
	]
];