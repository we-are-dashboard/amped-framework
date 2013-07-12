<?php
class mvc_models_users{
	public static function getUserInfo($userId){
		$db = api_service_DB::get();$usersT = Config::$_['TABLES']['users'];
		return $db->fetchRow("SELECT * FROM $usersT WHERE id=?", array($userId));
	}
	
	/** Register a New User
	 * @param String $name User Name
	 * @param String $email User's email. Should make sure on the front end only about verify.
	 * @param String $password User's password. Should make sure on the front end only about verify.
	 * @return int The User ID */
	public static function addUser($email, $password){
		if(strlen($password) < 4)
			throw new Exception("Password has to be 4 characters or more!", 'password');
		
		$db = api_service_DB::get();
		
		$sql = sprintf("INSERT INTO %s (email,password) VALUES (?,?)",
			Config::$_['TABLES']['users']);
		
		$db->query($sql,array($email, mvc_libs_Auth::hashPassword($email,$password)));
		return $db->lastInsertId();
	}
	
	/** Get a User By Id
	 * @param Int/String $id The id of the user or the username
	 */
	public static function getUser($id){
		$db = api_service_DB::get();
		
		if ( is_numeric($id))
			$sql = sprintf("SELECT * FROM %s WHERE id=?",
				Config::$_['TABLES']['users']);
		else
			$sql = sprintf("SELECT * FROM %s WHERE username=?",
				Config::$_['TABLES']['users']);
		
		$result = $db->fetchAll($sql, array($id));
		if ( count($result) == 0  )
			throw new Exception("Sorry no user with that name or id was found", '1200');
		else
			$result[0]['num_images'] = mvc_models_images::getInstance()->getNumImages($result[0]['id']);
		return count($result) > 0 ? $result[0] : false;
	}
	
	/** Add New User from a dynamic array.
	 * 	(because no password, can't signin until finished up registering the profile)
	 * @param Array $userFields. The array can only have one of:
	 * 	(username, email, name, profile_image, phone, location, bio, website)
	 * @return int The User ID */
	public static function addUserDynamic($userFields){
		$db = api_service_DB::get();
		
		$allowedFields = array('username'=>true, 'email'=>true, 'first_name'=>true, 'last_name'=>true,
			'profile_image'=>true, 'phone'=>true, 'location'=>true, 'bio'=>true, 'website'=>true);
		$sqlFields = $sqlBinds = '';
		$sqlVals = array();
		
		foreach($userFields as $field=>$v){
			if(!isset($allowedFields[$field]))
				throw new Exception("Field '$field' is not allowed to be added for dynamic users");
			$sqlFields .= ",$field";
			$sqlBinds .= ',?';
			$sqlVals[] = $v;
		}
		$sqlFields = trim($sqlFields, ',');
		$sqlBinds = trim($sqlBinds, ',');
	
		$sql = sprintf("INSERT INTO %s (%s) VALUES (%s)",
				Config::$_['TABLES']['users'], $sqlFields, $sqlBinds);
	
		$db->query($sql, $sqlVals);
		return $db->lastInsertId();
	}
	
	/** @todo Need to test it and also make sure that userId vs some kind of user hash
	 * Update User profile from a dynamic array. 
	 * @param Array $userFields. The array can only have one of:
	 * 	(email, password, name, profile_image, phone, location, bio, website)
	 * @return int The User ID */
	public static function updateUserDynamic($userId, $userFields){
		$user = Zend_Auth::getInstance()->getIdentity();
		if($user['id']!=$userId)
			throw new Exception("Can't update user!");
			
		$db = api_service_DB::get();
	
		$allowedFields = array('email'=>true, 'password'=>true, 'name'=>true, 'profile_image'=>true,
				'phone'=>true, 'location'=>true, 'bio'=>true, 'website'=>true);
		$sqlSet = '';
		$sqlVals = array();
	
		foreach($userFields as $field=>$v){
			if(!isset($allowedFields[$field]))
				throw new Exception("Field '$field' is not allowed to be added for dynamic users");
			$sqlSet .= "$field=?, ";
			$sqlVals[] = $v;
		}
		trim($sqlSet, ',');
	
		$sql = sprintf("UPDATE %s SET %s WHERE id=?",
				Config::$_['TABLES']['users'], $sqlSet);
	
		$db->query($sql, $sqlVals);
	}
	
	/** Check if an email exists.
	 * @param string $email The email to check.
	 * @return bool Does the email exist? Throws an exception on error. */
	public static function emailExists($email){
		$db = api_service_DB::get();
		$emailExists = $db->select();
		
		$emailExists->from(Config::$_['TABLES']['users'], 
							array('email_exists' => new Zend_Db_Expr('COUNT(1)')))
					 ->where('LOWER(email) = ?', trim(strtolower($email)))
					 ->limit(1);
		return (bool) $db->fetchOne($emailExists);
	}
	
	/** send email reset */
	public static function sendResetEmail($name, $email, $token, $html){
		$usersT = Config::$_['TABLES']['users'];
		$db = api_service_DB::get();
		if(!self::emailExists($email))
			throw new Exception("We don't recognize this email, please enter it again.");

		$subject = 'Your FusPic password has been reset.';

		Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Smtp('localhost'));
		$mail = new Zend_Mail('utf-8');
		$mail->addTo($email, $name);
		$mail->setFrom('admin@fuspic.com', 'FusPic');
		$mail->setSubject($subject);
		$mail->setBodyHtml($html);
		$mail->send();

		$db->update($usersT, array('reset_token'=>$token), "email=".$db->quote($email));
	}
	
	public static function getNameFromEmail($email){
		$db = api_service_DB::get(); $usersT = Config::$_['TABLES']['users'];
		return $db->fetchOne("SELECT name FROM $usersT WHERE email=?", array($email));
	}
	
	/** @todo FINISH THIS PROPERLY. There's a major bug here */
	public static function getInfoFromResetToken($email, $token){
		$db = api_service_DB::get(); $usersT = Config::$_['TABLES']['users'];
		return $db->fetchOne("SELECT * FROM $usersT WHERE email=?", array($email));
	}

	/** Reset a user's password
	 * @param string $email The email
	 * @param string $token The token 
	 * @param string $password The password */
	public static function resetPassword($email, $token, $password){
		$db = api_service_DB::get(); $usersT = Config::$_['TABLES']['users'];
		// check email and token
		$userCount = $db->fetchOne("SELECT COUNT(1) FROM $usersT WHERE email=? AND reset_token=?", array($email, $token));
		if(!is_string($token) || strlen($token)<10 || $userCount!='1')
			throw new Exception("This password reset has expired. Please contact us and we will be glad to help you");
			
		$db->query("UPDATE $usersT SET password=?, reset_token=0 WHERE email=? AND reset_token=?",
			array(mvc_libs_Auth::hashPassword($email, $password), $email, $token));
	}
	
	public static function isUsernameTaken($username){
		$db = api_service_DB::get(); $usersT = Config::$_['TABLES']['users'];
		return $db->fetchOne("SELECT COUNT(1) FROM $usersT WHERE username=? LIMIT 1", array($username));
	}
	
	public static function authenticateNetworkUser($userId, $network, $networkProfileId){
		$db = api_service_DB::get(); 
		$usersT = Config::$_['TABLES']['users'];
		$userNetworksT = Config::$_['TABLES']['user_networks'];
		return $db->fetchOne("SELECT COUNT(1) FROM $usersT AS u, $userNetworksT AS un
			WHERE un.user_id=u.id AND un.user_id=? AND un.network=? AND un.network_profile_id=? LIMIT 1",
			 array($userId, $network, $networkProfileId)); 
	}
	
	// This deals with Temp Users
	public static function getTempUser(){
		
	}
}


