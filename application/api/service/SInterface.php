<?php
abstract class api_service_SInterface
{
	protected $_service = null;
	
	public function __construct($service)
	{
		$this->_service = $service;
	}
	
	abstract public function run();
	
	abstract public function echoOutput();
}