<?php

class mvc_service_Request{
	protected static $_instance = null;
	
	/** Singleton instance
	 * @return mvc_service_Router */
	public static function getInstance(){
		if(null === self::$_instance){
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	/** Retrieve a member of the $_SERVER superglobal
	 * If no $key is passed, returns the entire $_SERVER array.
	 * @param $key string
	 * @param $default mixed Default value to use if key not found
	 * @return mixed Returns null if key does not exist */
	public function getServer($key = null, $default = null){
		if(null === $key){
			return $_SERVER;
		}
		
		return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
	}
	
	/** Return the method by which the request was made
	 * @return string */
	public function getMethod(){
		return $this->getServer('REQUEST_METHOD');
	}
	
	/** Return the value of the given HTTP header.
	 * Pass the header name as the
	 * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
	 * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
	 * @param $header string HTTP header name
	 * @return string false header value, or false if not found
	 * @throws Exception */
	public function getHeader($header){
		if(empty($header))
			throw new Exception('An HTTP header name is required');
		
		// Try to get it from the $_SERVER array first
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if(isset($_SERVER[$temp])){
			return $_SERVER[$temp];
		}
		
		// This seems to be the only way to get the Authorization header on
		// Apache
		if(function_exists('apache_request_headers')){
			$headers = apache_request_headers();
			if(isset($headers[$header])){
				return $headers[$header];
			}
			$header = strtolower($header);
			foreach($headers as $key=>$value){
				if(strtolower($key) == $header){
					return $value;
				}
			}
		}
		
		return false;
	}
	
	/** Was the request made by POST?
	 * @return boolean */
	public function isPost(){
		if('POST' == $this->getMethod()){
			return true;
		}
		
		return false;
	}
	
	/** Was the request made by GET?
	 * @return boolean */
	public function isGet(){
		if('GET' == $this->getMethod()){
			return true;
		}
		
		return false;
	}
	
	/** Was the request made by PUT?
	 * @return boolean */
	public function isPut(){
		if('PUT' == $this->getMethod()){
			return true;
		}
		
		return false;
	}
	
	/** Was the request made by DELETE?
	 * @return boolean */
	public function isDelete(){
		if('DELETE' == $this->getMethod()){
			return true;
		}
		
		return false;
	}
	
	/** Was the request made by HEAD?
	 * @return boolean */
	public function isHead(){
		if('HEAD' == $this->getMethod()){
			return true;
		}
		
		return false;
	}
	
	/** Was the request made by OPTIONS?
	 * @return boolean */
	public function isOptions(){
		if('OPTIONS' == $this->getMethod()){
			return true;
		}
		
		return false;
	}
	
	/** Is the request a Javascript XMLHttpRequest?
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 * @return boolean */
	public function isXmlHttpRequest(){
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}
	
	/** Is this a Flash request?
	 * @return boolean */
	public function isFlashRequest(){
		$header = strtolower($this->getHeader('USER_AGENT'));
		return (strstr($header, ' flash')) ? true : false;
	}
	
	/** Is https secure request
	 * @return boolean */
	public function isSecure(){
		return ($this->getScheme() === self::SCHEME_HTTPS);
	}

}