<?php

class mvc_libs_Auth{
	/** Generates a 128-character hash based on the user's login credentials.
	 * @param string $email User's login email.
	 * @param string $password User's login password.
	 * @return string 128-character hash. */
	public static function hashPassword($email, $password){
		// Generate salt
		$salt = hash("sha512", $email . "(><{~`|$}}}.?/@#$%&*(0928{[[");
		// Generate hash
		$hash = hash("sha512", $password . "(><{~`|$}}}.?/@#$%&*(0928{[[" . $salt);
		return $hash;
	}
	
	protected static function adapter($email, $password){
		$db = api_service_DB::get();
		$authDb = new Zend_Auth_Adapter_DbTable(
			$db,
			Config::$_['TABLES']['users'],
			'email',
			'password',
			'is_active = 1'
		);
		
		$authDb->setIdentity(strtolower($email));
		$authDb->setCredential(self::hashPassword(strtolower($email), $password));
		
		return $authDb;
	}
	
	protected static function userInfo(Zend_Auth_Adapter_DbTable $authDb, $email){
		$aUser = (array) $authDb->getResultRowObject(
			null,array('email', 'password', 'is_active'));

		$aUser['email'] = $email;
		foreach ($aUser as &$userField)
			if($userField == null)
				$userField = '';
		
		return $aUser;
	}
	
	/** Attempts to log the user. If successful, user data is written to the global Zend_Auth object.
	 * @param string $email User's login email.
	 * @param string $password User's login password.
	 * @return bool Specifies if login was successful. */
	public static function loginUser($email, $password){	
		$auth = Zend_Auth::getInstance();
		$authDb = self::adapter($email, $password);

		if ( ! $auth->authenticate($authDb)->isValid())
			return false;
		
		$aUser = self::userInfo($authDb, $email);

		$auth->getStorage()->write($aUser);
		api_service_DB::close();
		return true;
	}
	
	public static function loginNetworkUser($apiInstance, $network, $userId, $networkProfileId){
		// double check that the given user is logged in to the given network
		switch($network){
			case 'facebook':
				$facebook = $apiInstance;
				$user = $facebook->getUser();
				if(!$user || $user != $networkProfileId)
					throw new Exception("The '$network' account wasn't signed in!");
			break;
			case 'twitter':
				/*@todo FINISH THIS ASAP */
				$twitter = $apiInstance;
				$user = $twitter->getUser();
				if(!$user || $user!=$networkProfileId)
					throw new Exception("The '$network' account wasn't signed in!");
			break;
		}
		if(!mvc_models_users::authenticateNetworkUser($userId, $network, $networkProfileId))
			throw new Exception("This '$network' account could not authenticate as a FusPic account!");
		
		$auth = Zend_Auth::getInstance();
		$aUser = (array) mvc_models_users::getUserInfo($userId);
		foreach ($aUser as &$userField)
			if($userField == null)
				$userField = '';

		$auth->getStorage()->write($aUser);
		return true;
	}
	
	/** Logs the user out clearing all session and auth info.
	 * @return void */
	static public function logoutUser(){
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		Zend_Session::destroy();
	}
	
}

