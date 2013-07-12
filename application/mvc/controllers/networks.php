<?php
/** Check out class comments in mvc_models_networks to find out how to implement 
 * a new social network */
class networks extends mvc_service_Action{
	
	public function facebookAction(){
		Zend_Session::start();
		$this->setMainLayout('layouts-socialnetworklogin');
		$this->view->socialNetwork = 'Facebook';
		
		require 'social/facebook/src/facebook.php';
		// Create our Application instance (replace this with your appId and secret).
		$facebook = new Facebook(array(
			'appId'  => Config::$_['networks']['facebook']['app_id'],
			'secret' => Config::$_['networks']['facebook']['secret'],
		));
		// Get User ID
		$this->view->user = $fbUser = $facebook->getUser();
		// We may or may not have this data based on whether the user is logged in.
		// If we have a $fbUser id here, it means we know the user is logged into
		// Facebook, but we don't know if the access token is valid. An access
		// token is invalid if the user logged out of Facebook.
		if ($fbUser) {
			// Proceed knowing you have a logged in facebook user who's authenticated.
			$this->view->logoutUrl = $facebook->getLogoutUrl();
			$this->view->userProfile = $fbUserProfile = $facebook->api('/me');
			$model = new mvc_models_networks($facebook, 'facebook', $fbUser, $fbUserProfile);
			$model->signIn();
		}else
			$this->view->loginUrl = $facebook->getLoginUrl();
		
	}
	
	public function twitterAction(){
		Zend_Session::start();
		$this->setMainLayout('layouts-socialnetworklogin');
		$this->setView('facebook');
		$this->view->socialNetwork = 'Twitter';
	
		require_once('social/twitter/twitteroauth/twitteroauth.php');
		// init an empty user
		$this->view->user = null;
		$twitter = new social_twitter_FPUtil(Config::$_['networks']['twitter']['consumer_key'],
				Config::$_['networks']['twitter']['consumer_secret']);
		
		$urlToks = mvc_service_Router::getUrl(true);
		// if the callback was called
		if(isset($urlToks[2]) && $urlToks[2]=='callback'){
			$twitter->setAccessTokens();
		}
		
		if(!$twitter->isUserLoggedIn()){
			$this->view->loginUrl = $twitter->getLoginUrl();
		}else{
			//$connection = $twitter->getConnection();
			/* If method is set change API call made. Test is called by default. */
			$this->view->user = $twitter->getUser();
			$this->view->userProfile = $twitter->getUserProfile();
			
			$model = new mvc_models_networks($twitter, 'twitter', 
				$this->view->user, $this->view->userProfile);
			$model->signIn();
		}
	
	}
	
	
}