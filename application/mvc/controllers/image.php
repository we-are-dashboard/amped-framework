<?php
class image extends mvc_service_Action  
{
	
	private $_model, $_token;
	
	/*public function postDispatch(){
		$this->setView('index');
		
	}*/
		
	public function indexAction(){
		$this->_token = explode('/', substr($_SERVER['REQUEST_URI'], 1));
		$this->_model = mvc_models_Images::getInstance();
		
		// If this is hash.extension then spit image and quit
		$this->_checkIfFile();
		$this->_model->logImgViewed($this->_token[1]);
		
		$this->view->imageInfo = $this->_model->getUserImageByHash($this->_token[1]);
		$this->view->imageUserInfo = mvc_models_users::getUser($this->view->imageInfo['user_id']);
		$this->view->userStream = mvc_models_images::getInstance()->getUserImageStream($this->view->imageUserInfo['id']);
 	}
 	
 	protected function _checkIfFile(){
 		$extPos = strpos($this->_token[1], '.');
 		if($extPos === false) return;
 		
 		$this->_model->logImgViewed(substr($this->_token[1], 0, $extPos), 'image-page');
 		
 		$file = Config::$_['PATHS']['uploads'] . $this->_token[1];
 		$this->setMainLayout(false);
 		$this->dontRenderView();
 		$info = new api_libs_InfoPic($file);
 		$fp = fopen($file, 'rb');
		header("Content-Type: {$info->getMime()}");
		header("Content-Length: " . filesize($file));
		fpassthru($fp);
		exit;
 	}
	
	public function reelAction(){
		$this->view->params = $this->getParams();
	}
	
	public function commentsAction(){
		$this->view->comments = mvc_models_images::getInstance()->getImageComments($this->getParam('image_id'));
	}
	
}
