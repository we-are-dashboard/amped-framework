<?php

class social_twitter_FPUtil{
	protected $_connection, $_key, $_secret, $_credentials;
	
	public function __construct($key, $secret){
		$this->_key = $key;
		$this->_secret = $secret;
	}
	
	/** If access tokens are not available that means that the user is not logged in */
	public function isUserLoggedIn(){
		return !(empty($_SESSION['twitter_access_token']) ||
			empty($_SESSION['twitter_access_token']['oauth_token']) ||
			empty($_SESSION['twitter_access_token']['oauth_token_secret']));
	}
	
	public function getLoginUrl(){
		// The user is not logged in to twitter so clear the access_token
		unset($_SESSION['twitter_access_token']);
		/* Build TwitterOAuth object with client credentials. */
		$connection = new TwitterOAuth($this->_key, $this->_secret);
		/* Get temporary credentials. */
		$callback = Config::get('SITE_URL').'/networks/twitter/callback';
		$request_token = $connection->getRequestToken($callback);
		/* Save temporary credentials to session. */
		$_SESSION['twitter_access_token']['temp_oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['twitter_access_token']['temp_oauth_token_secret'] = $request_token['oauth_token_secret'];
			
		/* If last connection failed don't display authorization link. */
		if($connection->http_code == 200)
			return $connection->getAuthorizeURL($token);
		else throw new Exception('Could not connect to Twitter. Refresh the page or try again later.');
	}
	
	/** This is supposed to be the callback which Twitter redirects to. It sets
	 * 	the access tokens in the session and returns the connection object.
	 * @return boolean|TwitterOAuth The Twitter connection object. */
	public function setAccessTokens(){
		/* If the temp_oauth_token is old redirect to the connect page. */
		if(isset($_REQUEST['oauth_token']) && $_REQUEST['oauth_token'] !==
				$_SESSION['twitter_access_token']['temp_oauth_token']){
			unset($_SESSION['twitter_access_token']);
			return false;
		}
		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$connection = new TwitterOAuth($this->_key, $this->_secret, 
				$_SESSION['twitter_access_token']['temp_oauth_token'],
				$_SESSION['twitter_access_token']['temp_oauth_token_secret']);
		/* Request access tokens from twitter */
		$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
		/* Remove no longer needed request tokens */
		unset($_SESSION['twitter_access_token']['temp_oauth_token']);
		unset($_SESSION['twitter_access_token']['temp_oauth_token_secret']);
		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		if (200 == $connection->http_code) {
			/* The user has been verified and the access tokens can be saved for future use
				Save the access tokens. Normally these would be saved in a database for future use. */
			$_SESSION['twitter_access_token'] = $access_token;
			$this->_connection = $connection;
		}else return false;
		
		return $connection;
	}
	
	public function getConnection(){
		if($this->_connection)
			return $this->_connection;
		
		/* Get user access tokens out of the session. */
		$access_token = $_SESSION['twitter_access_token'];
		/* Create a TwitterOauth object with consumer/user tokens. */
		return $this->_connection = new TwitterOAuth($this->_key, $this->_secret,
				$access_token['oauth_token'],
				$access_token['oauth_token_secret']);
	}
	
	public function getUser(){
		if(!$this->isUserLoggedIn())
			return null;
		
		if(!$this->_credentials)
			$this->_credentials = $this->getConnection()->get('account/verify_credentials');
		
		if(!isset($this->_credentials->id_str))
			return null;
		
		return $this->_credentials->id_str;
	}
	
	public function getUserProfile(){
		if(! ($this->_credentials || $this->_getUser()) )
			return null;
		
		$creds = $this->_credentials;
		return array(
			'username'=>$creds->screen_name,
			'first_name'=>$creds->name,
			'location'=>$creds->location,
			//'lang'=>$creds->lang,
			'website'=>$creds->url,
			'bio'=>$creds->description,
			'profile_image'=>$creds->profile_image_url
		);
	}
	
}