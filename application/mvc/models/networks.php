<?php
/** The 2 social networks supported so far are:
 * facebook
 * twitter 
 * If you need to add a network you need to add the following methods:
 * 	1- In mvc/views/user/loginform.phtml add a "signin with" <a> tag. See the one for facebook
 * 	2- In mvc/controllers/networks.php add a new action for the network and its corresponding view
 * 	3- In mvc_models_networks::_createUserFrom{SocialNetwork} , see _createUserFromFacebook
 * 	4- add a switch case to mvc_libs_Auth::loginNetworkUser
 *  */
class mvc_models_networks{
	
	protected static $_instance = null;
	protected $_db, $_socialNetworkApiInstance, $_socialNetwork, $_networkProfileId, 
		$_networkProfile, $_userNetwork, $_userId;
	
	public function __construct($apiInstance, $socialNetwork, $networkProfileId, $networkProfile){
		$this->_db = api_service_DB::get();
		$this->_socialNetworkApiInstance = $apiInstance;
		$this->_socialNetwork = $socialNetwork;
		$this->_networkProfileId = $networkProfileId;
		$this->_networkProfile = $networkProfile;	
	}
	
	/** Singleton instance (DON'T NEED IT ALL I THINK, SINCE THERE COULD BE MORE THAN ONE NETWORK
	 * DELETE AT FIRST CHANCE)
	public static function getInstance(){
		if (null === self::$_instance)
			self::$_instance = new self();
		return self::$_instance;
	}*/
	
	/** Checks and manages the user_networks and registers/signs-in the user
	 * @param String $socialNetwork. One of the supported social networks.
	 * @param StrInt $networkProfileId. The actual profile id taken by OAuth of the social network.
	 * 	eg. facebook id or twitter profile id
	 * @param Array $networkProfile
	 */
	public function signIn(){
		// Get existing user if signed in already with a fuspic account
		$existingUser = Zend_Auth::getInstance()->getIdentity();
		// check if this Network Profile already exists in fus_user_networks
		if($this->_isUserNetwork() == 1){
			$this->_userNetwork = $this->_getUserNetwork();
			$this->_updateSocialNetworkProfile($this->_userNetwork['id']);
			$this->_userId = $this->_userNetwork['user_id'];
		}elseif($this->_isUserNetwork() == 0){
			$this->_userId = ($existingUser) ? $existingUser['id'] :
				$this->_createUserFromNetwork();
			$this->_addSocialNetworkProfile($this->_userId);
			$this->_userNetwork = $this->_getUserNetwork();
		}else
			throw new Exception('Same social network profile more than once ?');
		
		/* if there was no existing user we need to sign in the user with the
			account created out of the social network profile */
		if(!$existingUser)
			mvc_libs_Auth::loginNetworkUser($this->_socialNetworkApiInstance, 
				$this->_socialNetwork, $this->_userId, $this->_networkProfileId);
	}
	
	protected function _isUserNetwork(){
		return $this->_db->fetchOne("SELECT COUNT(1) FROM ".Config::$_['TABLES']['user_networks']." 
				WHERE network=? AND network_profile_id=?", 
			array($this->_socialNetwork, $this->_networkProfileId));
	}
	
	protected function _getUserNetwork(){
		return $this->_db->fetchRow("SELECT * FROM ".Config::$_['TABLES']['user_networks']."
				WHERE network=? AND network_profile_id=?",
			array($this->_socialNetwork, $this->_networkProfileId));
	}
	
	protected function _updateSocialNetworkProfile($userNetworkId){
		$this->_db->query("UPDATE ".Config::$_['TABLES']['user_networks']."
				SET serialized_network_profile=? WHERE id=?", 
			array(serialize($this->_networkProfile), $userNetworkId));
	}
	
	protected function _addSocialNetworkProfile($userId){
		$this->_db->query("INSERT INTO ".Config::$_['TABLES']['user_networks']."
				(user_id, network, network_profile_id, serialized_network_profile) VALUES(?,?,?,?)",
			array($userId, $this->_socialNetwork, $this->_networkProfileId, 
				serialize($this->_networkProfile)));
	}
	
	/** Creates a new FusPic user account from a registered Social Network account
	 * 	At this point we assume that there was no way to associate this user with
	 * 	an existing FusPic user account so we create a brand new one using the social
	 * 	network's info. */
	protected function _createUserFromNetwork(){
		//@todo add lang in 'fus_users' and grab lang from all networks
		$dynamicMethod = "_createUserFrom".ucwords(strtolower($this->_socialNetwork));
		return $this->{$dynamicMethod}();
	}
	
	/** @return Ambigous <number, string> The new created user_id */
	protected function _createUserFromFacebook(){
		$userFields = array();
		// username is mandatory in the users table, so need to make the impossible and create one that is not taken
		$username = $u = (isset($this->_networkProfile['username'])) ?
			$this->_networkProfile['username'] : str_replace(' ', '_', $this->_networkProfile['name']);
		$hashLength = 5;
		while(mvc_models_users::isUsernameTaken($username)){
			$username = $u.'_'.mvc_libs_Utils::getQuickHash($hashLength++);
		}
		$userFields['username'] = $username;
		// get the first_name
		if(isset($this->_networkProfile['first_name'])) 
			$userFields['first_name'] = $this->_networkProfile['first_name'];
		if(isset($this->_networkProfile['last_name']))
			$userFields['last_name'] = $this->_networkProfile['last_name'];
		if(isset($this->_networkProfile['locale']))
			$userFields['location'] = $this->_networkProfile['locale'];
		/*@todo REPLACE THE IMAGE LINK WITH A FUSPIC ONE */
		$userFields['profile_image'] = "http://graph.facebook.com/{$this->_networkProfile['id']}/picture";
		
		return mvc_models_users::addUserDynamic($userFields);
	}
	
	/** @todo obviously finish this up */
	protected function _createUserFromTwitter(){
		$userFields = $this->_networkProfile;
		$username = $u = $this->_networkProfile['username'];
		$hashLength = 5;
		while(mvc_models_users::isUsernameTaken($username)){
			$username = $u.'_'.mvc_libs_Utils::getQuickHash($hashLength++);
		}
		$userFields['username'] = $username;
		return mvc_models_users::addUserDynamic($userFields);
	}
	

	
}
