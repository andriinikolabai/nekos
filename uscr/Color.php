<?php
class Color {
	private $red;
	private $green;
	private $blue;
	
	public static function parseHexRgbString($color) {
		$color = strtolower($color);
		$color = preg_replace('/[^0-9a-f]/i', '', $color);
		$len = strlen($color);
		
		if($len > 6) {
			$color = substr($color, 0, 6);
		} else if($len < 6) {
			$color = str_repeat('0', 6 - $len) . $color;
		}
		
		$rgbHexArr = str_split($color, 2);
		$red = hexdec($rgbHexArr[0]);
		$green = hexdec($rgbHexArr[1]);
		$blue = hexdec($rgbHexArr[2]);
		
		return new Color($red, $green, $blue);
	}
	
	private function __construct($r, $g, $b) {
		$this->red = $r;
		$this->green = $g;
		$this->blue = $b;
	}
	
	public function getRedDec() {
		return $this->red;
	}
	
	public function getGreenDec() {
		return $this->green;
	}
	
	public function getBlueDec() {
		return $this->blue;
	}
}
?>
