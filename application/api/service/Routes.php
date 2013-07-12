<?php

class api_service_Routes{
	private static $_instance;
	private $noURI=false, $action=null;
	
	public function __construct(){
		$this->setupRoutes();
	}
	
	public static function getInstance(){
		if(isset(self::$_instance))
			return self::$_instance;
		
		return self::$_instance = new self();
	}
	
	private function setupRoutes(){
		
		$r = substr($_SERVER['REQUEST_URI'], -1) == '/' ? $_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'] . '/';
		if($r == '/'){
			$this->noURI = true;
			return;
		}
		
		$action = trim(substr($r, 0, strpos($r, '/', 2)), '/');
		
		switch ($action) {
			case 'image': $this->action = 'Imager'; break;
			case 'user': $this->action = 'Users'; break;
			case 'upload': $this->action = 'Uploader'; break;
			case 'i': $this->action = 'Imager'; break;
		}
	}
	
	public function getInterface(){
		if($this->noURI)
			//header('Location: '.Config::get('SITE_URL')); exit;} // need to put comment on top of api.fuspic.com
			throw new Exception("Come on son, you can be more creative than that! Give me an action!");
		elseif(! $this->action)
			throw new Exception("Action does not exist");
		else
			return $this->action;
	}
	
}
