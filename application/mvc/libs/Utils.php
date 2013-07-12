<?php
/** Class with bunch of static useful utilities.
 * Please check that utility is not available here b4 writing your own.
 * @author haknick */  
class mvc_libs_Utils{
	
	public static function getQuickHash($hashLength=6){
		$hashArr = array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i",
				"j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E",
				"F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$len = count($hashArr) - 1;
		$hash = '';
		for($i=0; $i < $hashLength; $i++)
			$hash .= $hashArr[rand(0, $len)];
		return $hash;
	}
}