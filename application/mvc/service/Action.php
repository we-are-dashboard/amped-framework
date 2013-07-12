<?php

/** Mirrors Zend_Controller_Action but much faster */
class mvc_service_Action
{
	public $view, $classMethods;
	protected $_renderView = true, $_actionName, $_ctrlName, $_params;
	
	public function __construct(){}
	
	/** Dispatch the requested action
	 * <b>For functionality similar to Action::forward please call View::action()
	 * 		right before returning from the current executing action. (Might need to reset
	 * 		some current action vars if necessessary)</b>
     * @param string $action. Method name of action
     * @param string $controller. Template Name.
     * @param array $params. Parameters passed from fus views or actions
     * @return void */
    public function dispatch($action, $controller, $params=array()){
    	$this->_actionName = $action;
    	$this->_ctrlName = $controller;
    	$this->_params = $params;
    	$this->view = new mvc_service_View($this);
    	//$this->setDefaultViewData();
        
		if (null === $this->classMethods)
			//flipping is much faster when checked often from the view
			$this->classMethods = array_flip(get_class_methods($this));
			
		// Call init() first
		$this->init();
			
		// Action methods have to finish with 'Action'
		$action = $action . 'Action';
		
		$this->preDispatch();

		// preDispatch() didn't change the action, so we can continue
		if (isset($this->classMethods[$action]))
		    $this->$action();
		else
		    throw new Exception("Action '$action' does not exist", 404);
			
		$this->postDispatch();
		
		if($this->_renderView)
			$this->view->render();
		
		//@todo is this needed ?
		//$this->postRender($viewContents);
    }
    
    public function getActionName(){
    	return $this->_actionName;
    }
    
	public function getControllerName(){
    	return $this->_ctrlName;
    }
    
    /** Retrieves a parameter from the instance. Priority is in the order of
     * 'fus parameters', '$_GET', '$_POST'. If a
     * parameter matching the $key is not found, default (null) is returned.
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @param string $sources. Values are one of: 'all', 'fus', 'get', 'post'
     * @return mixed */
    public function getParam($key, $default = null, $sources='all'){
        switch ($sources) {
            case 'fus':
                $default = (isset($this->_params[$key])) ?  $this->_params[$key] : $default;
            break;
            
            case 'get':
                $default = (isset($_GET[$key])) ?  $_GET[$key] : $default;
            break;
            
            case 'post':
                $default = (isset($_POST[$key])) ?  $_POST[$key] : $default;
            break;
            
            case 'all':
                if    (isset($this->_params[$key]))     $default = $this->_params[$key];
                elseif(isset($_GET[$key]))              $default = $_GET[$key];
                elseif(isset($_POST[$key]))             $default = $_POST[$key];
            break;
        }

        return $default;
    }

    /** Retrieves a merged array of parameters, with precedence of 'fus params', 
     * 	'$_GET', '$_POST' (i.e., values in the fus params will take precedence over all others).
     * @return array */
    public function getParams($sources='all'){
        switch ($sources) {
            case 'fus':
                return $this->_params; break;
            
            case 'get':
                return $_GET; break;
            
            case 'post':
                return $_POST; break;
            
            case 'all':
                return $this->_params + $_GET + $_POST; break;
        }
    }
    
    /** Set the main layout to a valid main layout template or false if you
     * don't want a main layout to render */
    protected function setMainLayout($mainLayout){
    	mvc_service_Front::getInstance()->setMainLayout($mainLayout);
    }
    
    protected function dontRenderView(){
    	$this->_renderView = false;
    }
    
	protected function setView($view){
    	$this->view->setViewLocation($view);
    }
	 
	 /** Override. */
	 protected function init() {}
    
    /** Override. Pre action dispatch routine */
    protected function preDispatch() {}
    
    /**
     * Override. Post-dispatch routines
     * Common usages for postDispatch() include applying changes for all of
     * template actions after the action has executed,
     * link url correction, setting headers, etc.
     * @return void
     */
    protected function postDispatch(){
    	$this->init();
	}
	
	protected function getRequest(){
		return mvc_service_Request::getInstance();
	}
    
}