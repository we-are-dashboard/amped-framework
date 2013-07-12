<?php

class mvc_service_View
{
	/** All the variables that SHOULD NOT be accessed by the view .phtml
	 *  should be elements of this array */
	private $_secret = array();
	protected $_viewLocation;
	  
	public function __construct(mvc_service_Action $action){
		$this->_secret['action'] = $action;
		$this->_viewLocation = mvc_service_Front::getInstance()->getViewsLoc() . 
			$action->getControllerName() .'/'. $action->getActionName() . '.phtml';
	}
	
	/** Method calls from the phtml are delegated to controllerAction calls of the running controller
	 * @param $name @todo finish this
	 * @param $args @todo finish desc */
	public function __call($name, $args){
		if(isset($this->_secret['action']->classMethods[$name]))
			call_user_func_array(
				array($this->_secret['action'], $name),$args);
		else
			throw new Exception("The method '$name'' is not a public method of ".
				"'".$this->_secret['action']->getControllerName()."'");
	}
	
	/** render the view's phtml */
	public function render(){
		$tmp = $this->_secret;
		unset($this->_secret);
		
		$this->_doRender();
		
		$this->_secret = $tmp;
	}
	
	/** very neat trick to hide non-phtml references 
	 * Ask @author Nik for clarification */ 
	private function _doRender(){
		include $this->_viewLocation;
	}
	
	/** Dispatch an action from the view object (inside the html, or inside the template action)
	 *  @param string $action. A public template action method without the ending 'Action' part
	 *  @param string $template. Valid template name.
	 *  @param array $params. An array of extra paramaters if desired */
	public function action($action, $controller, $params=array()){
		return mvc_service_Front::getInstance()->dispatchAction($action, $controller, $params);
	}
	
	/** Gets all the aggregated current action html */
	public function content(){
		return mvc_service_Front::getInstance()->getCurrentActionHtml();
	}
	
	public function setViewLocation($viewLocation){
		$this->_viewLocation = mvc_service_Front::getInstance()->getViewsLoc() . 
			$this->_secret['action']->getControllerName() .'/'. $viewLocation . '.phtml';
	}
	
}