<?php

class Service
{
    public static function run(){
        //setup include paths
        set_include_path(get_include_path()
        	. PATH_SEPARATOR . ROOT_PATH . '/application/mvc'
        	. PATH_SEPARATOR . ROOT_PATH . '/application/weblibs'
        	. PATH_SEPARATOR . ROOT_PATH . '/application'
        );
        
        // AUTOLOADER - Set up autoloading.
		// This is a nifty feature that allows us to load classes automatically so
		// that you don't have to litter your code with 'include' or 'require'
		// statements. From this point on, ideally no require or includes are needed.
        spl_autoload_register('Service::_autoload');
        		
		// Load the config_section from the environment variable
        $configSection =  getenv('config_section') ? getenv('config_section') : 'live';
        // Load configuration by section
        Config::setSection($configSection);
		// Init, set and configure Front
		$front = mvc_service_Front::getInstance();
		mvc_service_Router::getInstance()->setRoutes(Config::get('routes'));
    	// run!
        try {
            $front->dispatch();
        } catch (Exception $e) {
        	/* @todo NEED TO FINISH THIS PROPERLY. Be able to:
        	 *  If Feedback::hasErrors then spit out json instead of template */
        	try{//throw $e;
	        	self::_loadErrorView($e);
        	}catch(Exception $e){
        		echo "<div>
		    		The error template is not setup properly:
		    		<br/>FIX IT ASAP
		    		<br/>The original error was: {$e->getMessage()}
		    		<p><pre>{$e->getTraceAsString()}</pre></p></div>"; 
        	}
        }
    }
    
    private static function _autoload($class){
    	//could speed stuff up with require_once here instead of spl_autoload
    	//return spl_autoload(str_replace('_', '/', $class));
    	require_once str_replace('_', '/', $class).'.php';
    }
	
    /** Set which Error View to render
     * The error view is set in mvc_service_Front->getInstance()->errorView
     * @param $e Exception */
	private static function _loadErrorView($e){
		$view = mvc_service_Front::getInstance()->errorView;
		// Make sure there are no slashes so that no can mess around with folders
		if($view != 'generic' && strpos($view, '/') !== false)
			throw new Exception("mvc_service_Front::errorView can not have slashes");
		
		$info = array(
			'isDebug'=>Config::get('DEBUG'), 
			'error'=>array('msg'=>$e->getMessage(), 'trace'=>$e->getTraceAsString()));
		// check for slashes
		self::_renderErrorView($info, $view);
	}
	
	private static function _renderErrorView($this, $view){
		include "views/errors/$view.phtml";
	}
}






