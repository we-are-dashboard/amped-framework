<?php

class mvc_service_Router
{
	protected static $_instance = null, $_urlStr, $_urlTokens;
	protected $_url, $_ctrl, $_routes;//, $_site;
	
	/** Singleton instance
     * @return mvc_service_Router */
    public static function getInstance(){
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public static function getUrl($tokenized=false){
    	if(self::$_urlStr == null){
    		$url = trim($_SERVER['REQUEST_URI'], '/');
			//handle get variables
			$qPos = strpos($url, '?');
			if($qPos !== false)
				$url = substr($url, 0, $qPos);
			
			self::$_urlStr = $url;
    	}
    	
    	if(self::$_urlTokens == null)
    		self::$_urlTokens = explode('/', self::$_urlStr);
    	
    	return ($tokenized) ? self::$_urlTokens : self::$_urlStr;
    }
    
    /**
     * Get the current controller which means an array of controller/action
     * Checks if the passed page is:
     *  1- root
     *  2- zend MVC style controller/action, adapted to match /!/controller/action
     * @return array The current page controller and action. CTRL stands for the tuple (controller,action)
     * @throws Exception */
	public function getController(){
		if($this->_ctrl)
			return $this->_ctrl;
			
		if(!$this->_url){
			$this->_url = $url = trim($_SERVER['REQUEST_URI'], '/');
			//handle get variables
			$qPos = strpos($url, '?');
			if($qPos !== false)
				$this->_url = substr($url, 0, $qPos);
		}
		
		return $this->_matchRoutes();
	}
	
	/** Set the routes to be matched.
	 * @param Array $routes. An array such as:
	 * 	array(
	 		'/blah/more/thanfoobaretcetc'=>array('route'=>'image-funny', 
	 			'desc'=>'This is a full url to be matched'),
			'/i/albums/*'=>array('route'=>'image-albums', 
				'desc'=>'This url has 2 tokens and 3rd is star, representing anything'),
			'/u/*'=>array('route'=>'u-index',
				'desc'=>'This url has 1 token and 2nd is star, the one above has precedence')),
			'/'=>array('route'=>'home-index', 'desc'=>'Root')
	 *	Where the key of the inner arrays is the actual url to be matched, 
	 *	'route' is controller-action and 'desc' is an optional description. 
	 *	ALSO VERY IMPORTANT the order in which the routes are matched is exactly as above */
	public function setRoutes($routes){
		$this->_routes = $routes;
	}
	
	protected function _matchRoutes(){
		$fullUrl = '/'.$this->_url;
		$urlToks = explode('/', $this->_url);
		if(isset($this->_routes[$fullUrl])){
			list($ctrl, $action) = $this->_stripRoute($fullUrl);
		}elseif(isset($this->_routes['/'.$urlToks[0].'/'.$urlToks[1].'/*'])){
			list($ctrl, $action) = $this->_stripRoute('/'.$urlToks[0].'/'.$urlToks[1].'/*');
		}elseif(isset($this->_routes['/'.$urlToks[0].'/*'])){
			list($ctrl, $action) = $this->_stripRoute('/'.$urlToks[0].'/*');
		}else{
			$ctrl = (isset($urlToks[0]) && $urlToks[0]) ? $urlToks[0] : 'home';
			$action = isset($urlToks[1]) ? $urlToks[1] : 'index';
		}
		
		return $this->_ctrl = array('controller'=>$ctrl, 'action'=>$action);
	}
	
	private function _stripRoute($route){
		$routeToks = explode('-', $this->_routes[$route]['route']);
		$ctrl = (isset($routeToks[0]) && $routeToks[0]) ? $routeToks[0] : 'home';
		$action = isset($routeToks[1]) ? $routeToks[1] : 'index';
		return array($ctrl, $action);
	}
	
}
