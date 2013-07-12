<?php
class upload extends mvc_service_Action{
	public $_u = null;
	
	public function __construct(){
		$this->_u = Zend_Auth::getInstance()->getIdentity();
		$this->_model = mvc_models_upload::getInstance();
	}
	
	public function indexAction(){
	}
	
	public function uploadbarAction(){
	}
	
	public function toolAction(){
		$this->view->hashes = null;
		$this->setMainLayout('layouts-uploadframe');
		if($this->getRequest()->isPost())
			$this->view->response = $this->toolsubmit();
		// setup user presence
	}
	
	/** @todo need to check for url instead of image and also for multiple images ? */
	public function toolsubmit(){
		mvc_service_Front::getInstance()->errorView = 'uploaderbar';
		require_once 'api/interfaces/Uploader.php';
		var_dump($_FILES);exit;
		try{
			$up = new Uploader(null, 'upload/single/');
			$up->run();
		}catch(Exception $e){
			throw $e;
		}
		
		$this->view->hashes = $hs = implode(',', $up->getHashes());
		
		// connect image ids with users
		$this->_associateWithUser($up->getHashes());
	}
	
	/** We're doing this here for now, might need to go under API later */
	private function _associateWithUser($upHashes){
		// The user could be "loggedin", "temp", "site" 
		if($this->_u){
			foreach($upHashes as $k=>$v)
				$this->_model->setUserImage($v);
		}else{
			// By this point the user is assumed as a 'temp' user
			
		}
	}

}
