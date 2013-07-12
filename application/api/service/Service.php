<?php
class Service
{
	//public static $_config = null;
	protected $_action = '';
	protected $_output;
	
	public static function run(){
		//setup include paths
        set_include_path(get_include_path()
        	. PATH_SEPARATOR . ROOT_PATH . '/application/api'
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
        
		new self();
	}
	
	/* The action depepends on URL vars */
	public function __construct()
	{
		$k = dirname(__FILE__);
		try{
			$this->initService();
		}catch(Exception $e){
			if(Config::get('DEBUG')){
				//$this->addError($e->getMessage());
				throw $e;
			}else{
				$this->addError('An internal error occurred! Please contact the administrator.');
				$this->logErrors($this->_action, $e->getMessage());
			}
		}
		
		//$this->echoOutput(); @todo evaluate if the interface should be responsible for this
	}
	
	protected function initService()
	{
		// init the output
		$this->initOutput();
		// set action
		$this->_action = api_service_Routes::getInstance()->getInterface();
		
		$actionFile = 'interfaces/'.$this->_action.'.php';
		if(! self::isReadable($actionFile))
			throw new Exception('Action does not exist');
		require_once $actionFile;
		// init the action interface
		$actionObj = new $this->_action($this);
		if(! ($actionObj instanceof api_service_SInterface) )
			throw new Exception("ActionObject is not an instance of SInterface");
			
		$actionObj->run();
		
		//@todo should echoOutput be called here or from within the action ?
	}
	
	/**
	 * This does the final output return. It takes a $_output data object
	 	@todo don't think it's needed and the interface should do this
	public function echoOutput()
	{
		var_dump($this->_output);
	}  */
	
	protected function initOutput()
	{
		$this->_output = new stdClass();
		$this->_output->success = false;
		$this->_output->errors = array();
		$this->_output->response = '';
		
	}
	
	public function setSuccess($bool)
	{
		$this->_output->success = $bool;
	}
	
	protected function setErrors($errors)
	{
		$this->_output->errors = $errors;
	}
	
	/**
	 * @param $errors string/array Error/s to be added. If a string
	 * then the error will be added to the array, if an array, it will be
	 * merged with the existing errors array.
	 */
	public function addError($error)
	{
		if(is_string($error))
			$this->_output->errors[] = $error;
		else
			$this->_output->errors += $error;
	}
	
	public function setResponse($response)
	{
		$this->_output->response = $response;
	}
	
	public function getOutput()
	{
		return $this->_output;
	}
	
	/**
     * Returns TRUE if the $filename is readable, or FALSE otherwise.
     * This function uses the PHP include_path, where PHP's is_readable()
     * does not.
     */
	public function isReadable($filename)
    {
        if (!$fh = @fopen($filename, 'r', true)) {
            return false;
        }
        @fclose($fh);
        return true;
    }

	public function logErrors($file, $errMsg)
	{
		if(Config::get('DEBUG_EMAIL'))
			error_log(Config::get('SITE_ID') . " - " . $file . ":\n\n" . 
				$errMsg . "\n\n" . print_r($GLOBALS, TRUE) . "\n\n", 1, Config::get('DEBUG_EMAIL'));
		error_log(Config::get('SITE_ID') . " - " . $file . ":\n\n" . 
			$errMsg . "\n\n" . print_r($GLOBALS, TRUE) . "\n\n");
	}
	
	private static function _autoload($class){
    	//could speed stuff up with require_once here instead of spl_autoload
    	return spl_autoload(str_replace('_', '/', $class));
    	//require_once str_replace('_', '/', $class).'.php';
    }
}
