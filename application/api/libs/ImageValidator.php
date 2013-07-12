<?php

/** It conforms to a Validator interface */
class api_libs_ImageValidator
{	
	public $params = array(
			"w" => "The width of the image",
			"h" => "The height of the image",
			"s" => "Shorthand to specify size wxh fotmat",
			"q" => "The quality of the image",
			"a" => "Whether to maintain the aspect ratio of the image",
			"b" => "Background color, if needed, of the image",
			"c" => "Crop to center. Makes the image not have white border if the aspect ratio is different",
			"x" => "The x offset where to crop the image from",
			"y" => "The y offset where to crop the image from",
			"r" => "The return type of the request");
	
	/** Validate all params in one shot */
	public function validate($key, $val){
		 $funcName = 'validate_'.$key;
		 return $this->{$funcName}($key, $val);
	}
	
	protected function validate_all($key, $params){
		if(!ctype_digit($params['w']))
			throw new Exception("The paramater 'w' ({$this->params['w']}) is invalid");
		
		if(!ctype_digit($params['h']))
			throw new Exception("The paramater 'h' ({$this->params['h']}) is invalid");
			
		if(!ctype_digit($params['x']))
			throw new Exception("The paramater 'x' ({$this->params['x']}) is invalid");
			
		if(!ctype_digit($params['y']))
			throw new Exception("The paramater 'y' ({$this->params['y']}) is invalid");
			
		if($params['x'] != '0' || $params['y'] != '0'){
			if($params['x'] == '0' || $params['y'] == '0' || $params['w'] == '0' || $params['h'] == '0')
				throw new Exception("If using 'x' or 'y' then all of 'w', 'h', 'x', 'y' should be set to positive integers");
		}
			
		if(!ctype_digit($params['q']) || $params['q'] > 100)
			throw new Exception("The paramater 'q' ({$this->params['q']}) is invalid");
			
		$rVals = array('image'=>true, 'xml'=>true, 'json'=>true);
		if(!isset($rVals[$params['r']]))
			throw new Exception("The paramater 'r' ({$this->params['r']}) is invalid");
			
		$boolVals = array('false'=>true, 'true'=>true);
		if(!isset($boolVals[$params['c']]))
			throw new Exception("The paramater 'c' ({$this->params['c']}) is invalid");
			
		if(!isset($boolVals[$params['a']]))
			throw new Exception("The paramater 'a' ({$this->params['a']}) is invalid");
			
		if($params['a'] == 'false')
			if($params['w'] == '0' || $params['h'] == '0')
				throw new Exception("Both 'w' and 'h' are required when 'a' is set to 'false'");
			
	}
}