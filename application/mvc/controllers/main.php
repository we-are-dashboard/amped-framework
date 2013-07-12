<?php
/** This controller contains snippet-actions that are used everywhere 
 * 	such as header, footer etc */
class main extends mvc_service_Action  
{
	
	public function footAction(){
	}
	
	public function headAction(){
		$this->_token = explode('/', substr($_SERVER['REQUEST_URI'], 1));
		$this->view->imageInfo = false;
		if ( $this->_token[0] == "i" )
			$this->view->imageInfo = mvc_models_Images::getInstance()->getUserImageByHash($this->_token[1]);
	}
	
	public function topbarAction(){
	}
	
}
