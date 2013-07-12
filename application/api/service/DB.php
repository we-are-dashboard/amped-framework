<?php
/** I EVALUATED THIS VERY  CAREFULLY and Zend_Db is a lot better in many levels.
 * 1- Binding query statements with ? is so easy and aids a lot in security in the long run.
 * 2- Lots of proven utility methods.
 * 3- If we ever need to change from MySQL to something like PostGre, we won't pull our hair.
 * 4- The only reason not to use it would have been premature optimization. Not a valid reason here at all. */
class api_service_DB{
	/** Global instance of the MySQL database object. */
	private static $_db;

	/** Gets a connection to the database. If the connection hasn't been created, it'll create one. */
	public static function get($charset = NULL){
	    if(self::$_db)
	        return self::$_db;
	    
		self::$_db = Zend_Db::factory('Pdo_Mysql', array(
				'host'=>Config::get('DB_HOST'), 'dbname'=>Config::get('DB_NAME'),
				'username'=>Config::get('DB_USER'), 'password'=>Config::get('DB_PASSWORD')));

		if (isset($charset))
		    self::$_db->query('SET NAMES "' . $charset . '"');

		return self::$_db;
	}
	
	/** Closes the active database connection. Not usually needed to be done manually.
	 * @return void
	 * @access public */
	public static function close(){
		if (self::$_db != null)
			self::$_db = null;
	}

	/** Gets a new connection to the database.
	 * ONLY use this if u need a totally new different connection than the singleton instance
	 * @return PDO The MySQL database connection object. */
	public function getNonSingletonConnection(){
		return Zend_Db::factory('Pdo_Mysql', array(
				'host'=>Config::get('DB_HOST'), 'dbname'=>Config::get('DB_NAME'),
				'username'=>Config::get('DB_USER'), 'password'=>Config::get('DB_PASSWORD')));
	}
}