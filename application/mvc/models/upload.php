<?php
class mvc_models_upload{
	protected static $_instance = null;
	private $_db;
	
	public function __construct(){
		$this->_db = api_service_DB::get();
	}
	
	public static function getInstance(){
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
	
		return self::$_instance;
	}
	
	public function setUserImage($imgHash){
		$u = Zend_Auth::getInstance()->getIdentity();
		$imagesModel = mvc_models_images::getInstance();
		$img = $imagesModel->getImageByHash($imgHash);
		
		$sql = sprintf("INSERT INTO %s (user_id,image_id) VALUES (?,?)",
				Config::$_['TABLES']['user_images']);
	
		$this->_db->query($sql,array($u['id'], $img['id']));
		return $this->_db->lastInsertId();
	}
	
	/*/ Not sure I need these 2 anymore now
	public static function setupUserPresence(){
		$guestToken = isset($_COOKIE['fp_gt']) ? $_COOKIE['fp_gt'] : false;
	
	}
	public static function validateGuest(){
		$guestToken = isset($_COOKIE['fp_gt']) ? $_COOKIE['fp_gt'] : false;
		
	}*/
	
}