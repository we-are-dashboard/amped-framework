<?php
/** --------------- CONFIG DATA ----section(){self::other();---------is how you inherit from other section---- */
class ConfigValues{
	protected static $configData = array(
		"GATEWAY" => "gateway.php",					// @TODO no idea what this is used for
		"SITE_ID" => "FUSPIC",						// Unique string identifier of your site
		"SITE_URL" => "http://fuspic.com",			// The Domain of your site
		"API_URL" => "http://api.fuspic.com",		// API URL (@TODO figure out if this is needed)
		"SHORT_API_URL" => "http://picf.us",		// Short URL (@TODO figure out how to use this)
		"DEBUG_EMAIL" => "haknick@gmail.com",		// Debug email (not sure what this will be used for)
		"FROM_ADDRESS" => "info@fuspic.com",		// Email from email address
		"FROM_NAME" => "FUSPIC",					// Email from name
		"DB_HOST" => "localhost",					// Database Host (When in doubt, localhost)
		"DB_USER" => "root",						// Your database username
		"DB_PASSWORD" => "",						// Your database password
		"DB_NAME" => "c0fuspic",					// Database name
		"DEBUG" => false,							// Debug flag
		"TABLES_PREFIX" => "amp_"					// Databases tbale prefix
	);
	
	protected static function live(){
		/*
		*	Databse tables
		*/
		// Item Tables
		self::$configData["TABLES"]["items"] = self::$configData['TABLES_PREFIX']."items";
		self::$configData["TABLES"]["item_response"] = self::$configData['TABLES_PREFIX']."item_response";			// Not sure what this table is for
		self::$configData["TABLES"]["item_comments"] = self::$configData['TABLES_PREFIX']."item_comments";
		self::$configData["TABLES"]["item_stats"] = self::$configData['TABLES_PREFIX']."item_stats";
		// User Tables
		self::$configData["TABLES"]["users"] = self::$configData['TABLES_PREFIX']."users";
		self::$configData["TABLES"]["user_networks"] = self::$configData['TABLES_PREFIX']."user_networks";
		self::$configData["TABLES"]["user_items"] = self::$configData['TABLES_PREFIX']."user_items";
		self::$configData["TABLES"]["user_followship"] = self::$configData['TABLES_PREFIX']."user_followship";
		// Misc Tables
		self::$configData["TABLES"]["verify_tokens"] = self::$configData['TABLES_PREFIX']."verify_tokens";

		/*
		*	Social Networks Info
		*/
		// To use the built in Facbeook and Twitter logins uncomment these lines
		// self::$configData["networks"]["facebook"]["app_id"] = "";
		// self::$configData["networks"]["facebook"]["secret"] = "";
		// self::$configData["networks"]["twitter"]["consumer_key"] = "";
		// self::$configData["networks"]["twitter"]["consumer_secret"] = "";
		// Image location for linking to it

		/*
		*	Routes
		*/
		// These are the pages for the site.
		// Routes There's general rules on how to build them in: mvc_service_Router::setRoutes
		// @TODO Get documentation here not in mvc_service_Router
		self::$configData["routes"]['/'] = array('route'=>'home-index', 'desc'=>'home');
		self::$configData["routes"]['/i/*'] = array('route'=>'image-index', 'desc'=>'image');
		self::$configData["routes"]['/u/*'] = array('route'=>'user-index', 'desc'=>'user');
		self::$configData["routes"]['/privacypolicy'] = array('route'=>'home-privacy');
		//self::$configData["routes"]['/upload'] = array('route'=>'upload-index');
		
		self::$configData["image_url"] = "";
	}
	protected static function stage(){self::live();
		ini_set('display_error', '1');
		self::$configData['DEBUG'] = true;
		self::$configData['SITE_URL'] = "http://stage.fuspic.com";
		self::$configData['API_URL'] = "http://stage.api.fuspic.com";
	}
	
	protected static function dev(){self::stage();
		self::$configData['SITE_URL'] = "http://local.ampedframework.com";
	}
	
	protected static function ted(){self::dev();
		//self::$configData["DB_HOST"] = "192.168.1.9";
		self::$configData["DB_USER"] = "root";
		self::$configData["DB_PASSWORD"] = "root";
		//self::$configData["DB_HOST"] = "localhost";
		self::$configData["DB_NAME"] = "c0fuspic_stage";
	}
	
}/** --------------- CONFIG DATA --------------------------------------- */
	
/** ---------------- Class to manage the configs ----------------------- */
class Config extends ConfigValues{	
	public static $_;
	
	public static function setSection($section){
		self::$section();
		self::$_ = self::$configData;
	}
	
	public static function get($key){
		return self::$configData[$key];
	}
}