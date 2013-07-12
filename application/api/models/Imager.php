<?php

class api_models_Imager{
	
	private $_db, $ratio, $image;
	private $_allowedParams = array('name'=>1, 'title'=>1, 'request_uri'=>1,
		'url'=>1, 'w'=>1, 'h'=>1, 'q'=>1, 'mime_type'=>1, 'source'=>1, 'status'=>1, 'extension'=>1);
	
	public function __construct($db){
		$this->_db = $db;
	}
	
	public function getImage(){
		$reqUri = (substr($_SERVER['REQUEST_URI'], -1) == '/') ? 
			$_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'] . '/';
		return $this->_db->fetchRow("SELECT * FROM fus_images WHERE request_uri=?", array($reqUri));
	}
	
	public function buildNewEntry($params){		
		$params['hash'] = $this->getUniqueKey();
		
		$sql = "INSERT into fus_images (hash, request_uri, url, w, h, q) 
			VALUES (?, ?, ?, ?, ?, ?)";
				 
		$this->_db->query($sql, array($params['hash'], $params['request_uri'],
				$params['url'], $params['w'], $params['h'], $params['q']));
		//$params['id'] = $this->_db->lastInsertId();
		return $params;
	}
	
	/** Insert new image using dynamic params
	 * @param Array $params Key=>Value array where the key should be the field name of 
	 * 	fus_images and the value should be the value. The query will be built dynamically 
	 * 	and only (w, h) keys are required */
	public function buildNewDynamicEntry($params){		
		$hash = $this->getUniqueKey();
		$values = $fields = array();
		foreach($params as $field=>$value){
			if(!isset($this->_allowedParams[$field]))
				throw new Exception("Wrong parameter '$field' !");
			$fields[] = $field;
			$values[] = $this->_db->quote($value);
		}
		
		$fieldsStr = implode(',', $fields);
		$valuesStr = implode(',', $values);
		
		$sql = "INSERT into fus_images (hash, $fieldsStr) VALUES ('$hash', $valuesStr)";
		$this->_db->query($sql);
		
		return $hash;
	}
	
	protected function getUniqueKey($iteration = 0, $maxIteration= 10){
		$keyArr = array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i",
			"j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E",
			"F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$len = count($keyArr) - 1;
		$key = '';
		for($i=0; $i < 6; $i++)
			$key .= $keyArr[rand(0, $len)];
		$prep = $this->_db->prepare(sprintf("SELECT hash FROM fus_images WHERE hash = %s ", $this->_db->quote($key)));
		$prep->execute();
		if ($prep->rowCount() > 0 ){
			if ( $iteration <= $maxIteration )
				$this->getUniqueKey($iteration+1, $maxIteration);
			//else
				//Throw exception for exceptions missed or just add another parameter for the length of the key
			return;
		} 
		
		return $key;
	}
	
}
