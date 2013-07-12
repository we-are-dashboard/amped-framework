<?php

class user extends mvc_service_Action{
	
	public function indexAction(){
		// action body
		$token = explode('/', substr($_SERVER['REQUEST_URI'], 1));
		$this->view->cred = Zend_Auth::getInstance()->getIdentity();
		$this->view->userInfo = mvc_models_users::getUser($token[1]);
		$this->view->userStream = mvc_models_images::getInstance()->getUserImageStream($token[1]);
	}
	
	public function settingsAction(){
	}
	
	public function loginAction(){
		//if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()){
			try{
				$this->_loginPost();
			}catch(Exception $e){
				$error = Config::get('DEBUG') ? $e->getMessage() :
					"There was an error it seems. We were notified and will be fixing it soon.";
				api_service_Feedback::getInstance()->addError($error);
			}
			echo api_service_Feedback::getInstance()->getJsonFeedback();
		//}
	}
	
	public function loginformAction(){
		$this->setMainLayout(false);
	}
	
	public function logoutAction(){
		mvc_libs_Auth::logoutUser();
		header("Location: /");
		exit();
	}
	
	private function _loginPost(){
		$this->setMainLayout(false);
		$this->dontRenderView();
		
		$p = $this->getParams();
		if(! isset($p['email']) || (strlen($p['email']) < 3))
			api_service_Feedback::getInstance()->addError("Invalid email!", 'email');
		if(! mvc_libs_Auth::loginUser($p['email'], $p['password']))
			api_service_Feedback::getInstance()->addError("Wrong email or password!");
		
		if(api_service_Feedback::getInstance()->hasErrors())
			return;
	}
	
	public function registerAction(){
		//if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()){
			try{
				$this->_registerPost();
			}catch(Exception $e){
				api_service_Feedback::getInstance()->addError("There was an error it seems. Please let us know of the issue!");
			}
			echo api_service_Feedback::getInstance()->getJsonFeedback();
		//}
	}
	
	public function registerformAction(){
		$this->setMainLayout(false);
	}
	
	private function _registerPost(){
		$this->setMainLayout(false);
		$this->dontRenderView();
		// replace those two with $this->json maybe
		$p = $this->getParams();
		/*@todo ADD USERNAME ASAP, CHANGE IT TO NOT NULL IN THE DB */
		if(! isset($p['email']) || (strlen($p['email']) < 3))
			api_service_Feedback::getInstance()->addError("Invalid email!", 'email');
		if(! isset($p['password']) || (strlen($p['password']) < 4))
			api_service_Feedback::getInstance()->addError("Password has to be 4 characters or more!", 'password');
		if(! isset($p['repeatpassword']) || ($p['password'] != $p['repeatpassword']))
			api_service_Feedback::getInstance()->addError("Password does not match!", 'repeatpassword');
		//if(! isset($p['privacy']) || ($p['privacy'] != '1'))
			//api_service_Feedback::getInstance()->addError("Please agree to the Privacy Policy", 'privacy');
		if(mvc_models_users::emailExists($p['email']))
			api_service_Feedback::getInstance()->addError("This email already exists!", 'email');
		
		if(api_service_Feedback::getInstance()->hasErrors())
			return;
		
		mvc_models_users::addUser($p['email'], $p['password'], '1');
		
		mvc_libs_Auth::loginUser($p['email'], $p['password']);
	}
	
	public function lostpassAction(){
		if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()){
			$this->setMainLayout(false);
			$this->dontRenderView();
			
			$fullUrl = Config::$_['paths']['SITE_URL'];
			$email = $this->getRequest()->getParam('email');
			$token = md5(rand() . '345$%^asd!@#1239' . rand());
			$this->view->name = $name = mvc_models_users::getNameFromEmail($email);
			$this->view->resetLink = $fullUrl . "user/resetpass/token/$token/email/" . base64_encode($email);
			$this->view->imagesUrl = $fullUrl . '/assets/images/email/reset/';
			
			$html = $this->view->render('user/emails/reset.phtml');
			
			try{
				mvc_models_users::sendResetEmail($name, $email, $token, $html);
			}catch(Exception $e){
				api_service_Feedback::getInstance()->addError($e->getMessage());
			}
			echo api_service_Feedback::getInstance()->getJsonFeedback();
		}
	}
	
	public function resetpassAction(){
		$this->view->token = $token = $this->getRequest()->getParam('token');
		$this->view->email = $email = $this->getRequest()->getParam('email');
		$this->view->isPost = false;
		if($this->getRequest()->isPost()){
			$this->view->isPost = true;
			$this->view->error = null;
			try{
				if($this->getRequest()->getParam('password') != $this->getRequest()->getParam('verify_password'))
					throw new Exception("Passwords don't match! Please try again.");
				
				$email = base64_decode($email);
				mvc_models_users::resetPassword($email, $token, $this->getRequest()->getParam('password'));
				
				mvc_libs_Auth::loginUser($email, $this->getRequest()->getParam('password'));
			
			}catch(Exception $e){
				$this->view->error = $e->getMessage();
			}
		}
	}
	
	public function barAction(){
		$this->view->user = $user = Zend_Auth::getInstance()->getIdentity();
		if($user)
			$this->view->user['username'] = $user['username'] ?  $user['username'] : $user['email'];
		if($this->getRequest()->isXmlHttpRequest())
			$this->setMainLayout(false);
	
	}
	public function credsAction(){
		$this->setMainLayout(false);
		$this->dontRenderView();
		echo json_encode(Zend_Auth::getInstance()->getIdentity());
	}

}
