#!/usr/bin/php
<?php

class Everlights {
	private $ip = false;
	private $socket = false;
	private $pattern = 0;
	private $sequence = [ "000000" ];
	
	const NONE = 0;
	const BLINK = 1;
	const TWINKLE = 6;
	const FADE = 4;
	const STROBE = 5;
	const CHASE_LEFT = 2;
	const CHASE_RIGHT = 3;
	
	const BLACK = [ 0, 0, 0 ];
	const RED = [ 255, 0, 0 ];
	const GREEN = [ 0, 255, 0 ];
	const WHITE = [ 255, 255, 90 ];
	
	public function __construct($ip) {
		$this->ip = $ip;
		$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	}
	
	public function pattern($num = false) {
		if ($num !== false) $this->pattern = $this->hexint($num);
		if ($this->pattern > 0) {
			$this->send("P00");
		}
		$this->send(sprintf("P%02s", $this->pattern));
	}
	
	public function sequence($seq = false) {
		if ($seq !== false) $this->sequence = $seq;
		$str = sprintf("C%02s", dechex(count($this->sequence)));
		foreach ($this->sequence as $color) {
			$str .= sprintf("%02s%02s%02s", $this->hexint($color[1]), $this->hexint($color[0]), $this->hexint($color[2]));
		}
		$this->send($str);
	}

	public function speed($num = 0) {
		$this->send(sprintf("S%02s", $this->hexint($num)));
	}
	
	public function brightness($num = 255) {
		$this->send(sprintf("I%02s", $this->hexint($num)));
	}
	
	public function on() {
		$this->send("O01");
	}
	
	public function off() {
		$this->send("O00");
	}
	
	private function send($msg) {
		echo "$msg\n";
		socket_sendto($this->socket, $msg, strlen($msg), 0, $this->ip, 8080);
		usleep(100000);
	}
	
	private function hexint($num) { 
		// Make sure it's between 0 and 255, then convert it to hex
		$num = max(0, min(255, intval($num)));
		return dechex($num);
	}
}






$SEQUENCES = [
	"christmas-1" => [
		"pattern" => Everlights::CHASE_RIGHT,
		"sequence" => [
			Everlights::RED,
			Everlights::RED,
			Everlights::RED,
			Everlights::WHITE,
			Everlights::WHITE,
			Everlights::WHITE,
			Everlights::GREEN,
			Everlights::GREEN,
			Everlights::GREEN,
			Everlights::WHITE,
			Everlights::WHITE,
			Everlights::WHITE,
		],
	],
	"christmas-2" => [
		"pattern" => Everlights::BLINK,
		"sequence" => [
			Everlights::RED,
			Everlights::RED,
			Everlights::WHITE,
			Everlights::WHITE,
			Everlights::GREEN,
			Everlights::GREEN,
		],
	],
	"christmas-3" => [
		"sequence" => [
			Everlights::RED,
			Everlights::WHITE,
			Everlights::GREEN,
		],
	],
	"christmas-random" => [
		"random" => [
			"christmas-1",
			"christmas-2",
			"christmas-3",
		],
	],
];

function usage() {
	global $SEQUENCES;
	echo "Usage: everlights.php <sequence>\n";
	echo "Possible sequences:\n";
	foreach ($SEQUENCES as $name => $info) {
		echo "\t- $name\n";
	}
	echo "\t- off\n";
	echo "\n";
}



$box = new Everlights("everlights.lan");

if (!isset($argv[1])) {
	usage();
	exit();
} else if ($argv[1] === "off") {
	$box->off();
	exit();
} else if (!isset($SEQUENCES[$argv[1]])) {
	usage();
	exit();
}



$seq = $SEQUENCES[$argv[1]];
if (isset($seq['random'])) {
	$num = mt_rand(0, count($seq['random']) - 1);
	$name = $seq['random'][$num];
	$seq = $SEQUENCES[$name];
	echo "Using random sequence: $name\n\n";
}


$box->off();
$box->on();
$box->sequence($seq['sequence']);

if (isset($seq['pattern'])) {
	$box->pattern($seq['pattern']);
} else {
	$box->pattern(0);
}

if (isset($seq['speed'])) {
	$box->speed($seq['speed']);
} else {
	$box->speed(0);
}

if (isset($seq['brightness'])) {
	$box->brightness($seq['brightness']);
} else {
	$box->brightness(255);
}

