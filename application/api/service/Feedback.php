<?php

class api_service_Feedback{
	private static $_self;
	private $_success=true, $_errors=array(), $_response = '';
	
	public static function getInstance(){
		if(!self::$_self)
			return self::$_self = new self();
			
		return self::$_self;
	}
	
	public function setSuccess($bool){
		$this->_success = $bool;
	}
	
	public function setErrors($errors){
		if($this->_success) $this->_success = false;
		$this->_errors = $errors;
	}
	
	public function addError($error, $field=null){
		if($this->_success) $this->_success = false;
		if($field) $this->_errors[$field] = $error;
			else $this->_errors[] = $error;
	}
	
	public function setResponse($response){
		$this->_response = $response;
	}
	
	public function hasErrors(){
		return !empty($this->_errors);
	}
	
	public function getJsonFeedback(){
		return json_encode(array('success'=>$this->_success, 'errors'=>$this->_errors, 'response'=>$this->_response));
	}
		
}