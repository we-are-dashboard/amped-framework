<?

class mvc_models_images{
	
	protected static $_instance = null;
	private $_db;
	private $_hashes = array();
	
	public function __construct(){
		$this->_db = api_service_DB::get();
	}
	
	public static function getInstance(){
		if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
	}
	
	public function getUserImageByHash($hash){
		if ( isset($this->_hashes[$hash])) return$this->_hashes[$hash];
		
		$sql = "SELECT * FROM fus_images, fus_user_images WHERE fus_images.hash=? AND fus_user_images.image_id=fus_images.id";
		$result = $this->_db->fetchAll($sql, $hash);
		
		if ( count($result) > 0 ){
			$this->_hashes[$hash] = $result[0];
			return $result[0];
		}
		
		return false;
	}
	
	public function getImageByHash($hash){
		$sql = "SELECT * FROM fus_images WHERE fus_images.hash=? ";
		$result = $this->_db->fetchAll($sql, $hash);
		return $result[0];
	}
	
	public function getUserImageStream($uid){
		
		$user_id = $uid;
		
		if (!is_numeric($uid)){
			$sql = sprintf("SELECT id FROM %s WHERE username=?",
				Config::$_['TABLES']['users']);
			$result = $this->_db->fetchAll($sql, array($uid));
			
			if ( count($result) == 0)
				throw new Exception("Sorry no image with that id was found", '1200');
			
			$user_id = $result[0]['id'];
		}
		
		$select = $this->_db->select()
				->from(array('usr_img'=>Config::$_['TABLES']['user_images']), '*')
				->join(array("img"=>Config::$_['TABLES']['images']),
						"usr_img.image_id = img.id")
				->where("user_id=?",$user_id);
		$prepare = $this->_db->query($select);		
		return $prepare->fetchAll();
	}
	
	public function getImageComments($imgid){
		
		$select = $this->_db->select()
				->from(array('comment'=>Config::$_['TABLES']['image_comments']))
				->join(array('usr'=>Config::$_["TABLES"]['users']),
						"comment.user_id=usr.id")
				->where("comment.image_id=?",$imgid);
		$prepare = $this->_db->query($select);
		$result = $prepare->fetchAll();
		
		$num_comments = $this->getNumImageComments($imgid);
		return array("comments"=>$result, "info"=>array('num_comments'=>$num_comments));	
	}
	
	public function getNumImages($uid){
		$sql = "SELECT COUNT(*) FROM fus_user_images WHERE user_id=?";
		$result = $this->_db->fetchAll($sql, $uid);
		return $result[0]["COUNT(*)"];
	}
	
	public function getNumImageComments($imgid){
		$sql = "SELECT COUNT(*) FROM fus_image_comments WHERE image_id=?";
		$result = $this->_db->fetchAll($sql, $imgid);
		return $result[0]["COUNT(*)"];
	}
	
	/** Log the image view
	 * @param $hash String The image hash
	 * @param $type String 'image' or 'image-page' */
	public function logImgViewed($hash, $type='image'){
		$imgInfo = $this->getImageByHash($hash);
		
		$sql = "SELECT * FROM fus_image_stats WHERE image_id=?";
		$imgStats = $this->_db->fetchRow($sql, $imgInfo['id']);
		
		if($imgStats === false){
			$this->_db->insert('fus_image_stats', array('image_last_viewed'=>new Zend_Db_Expr('NOW()'),
					'image_page_last_viewed'=>new Zend_Db_Expr('NOW()'), 'image_id'=>$imgInfo['id']));
		}else{
			$up = ($type=='image') ? 
				array('image_views'=>(intval($imgStats['image_views']) + 1),
					'image_last_viewed'=>new Zend_Db_Expr('NOW()')) :
				array('image_page_views'=>(intval($imgStats['image_page_views']) + 1),
					'image_page_last_viewed'=>new Zend_Db_Expr('NOW()'));
						
				$this->_db->update('fus_image_stats', $up, array('image_id=?'=>$imgInfo['id']));
		}
	}
	
}






