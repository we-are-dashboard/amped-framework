<?php

/** @todo EVALUATE IF THIS IS NEEDED. There is a good chance that there's no need for such a class */
class mvc_service_Site{
	protected static $_instance = null;
	protected $_settings;
	
	/** Singleton instance */
    public static function getInstance(){
        if (null === self::$_instance) 
            self::$_instance = new self();
        return self::$_instance;
    }
	
	public function __construct(){
		$this->_db = api_service_DB::get();
	}
	
}