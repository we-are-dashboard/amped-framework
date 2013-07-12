<?php
class Uploader extends api_service_SInterface
{	
	/** $_upLoc -> upload location */
	private $_requestURI, $_params = array(), $_returnType, $_model, $_upLoc, $_infoPic, $_newHashes;
	
	public function __construct($service, $requestURI=null){
		parent::__construct($service);
		$this->_model = new api_models_Imager(api_service_DB::get());
		$this->_upLoc = Config::$_['PATHS']['uploads'];
		$this->_requestURI = $requestURI ? $requestURI : $_SERVER['REQUEST_URI'];
	}
	
	public function echoOutput(){
		var_dump('testing');
	}
	
	public function run(){
		$this->_params = $p = explode('/', $this->_requestURI);
		$type = "_{$p[1]}Upload";
		if(method_exists($this, $type))
			$this->$type();
	}
	
	public function getHashes(){
		return $this->_newHashes;
	}
	
	/** Handle upload and return data as per protocol passed or call the callback */
	protected function _singleUpload(){
		$this->_infoPic = new api_libs_InfoPic($_FILES['userfile']['tmp_name']);
		$params['name'] = $_FILES['userfile']['name'];
		$params['w'] = $this->_infoPic->getW();
		$params['h'] = $this->_infoPic->getH();
		$params['mime_type'] = $this->_infoPic->getMime();
		$params['source'] = 'site_upload_single';
		$params['status'] = 'live';
		$params['extension'] = $this->_infoPic->getExt();
		
		$hash = $this->_model->buildNewDynamicEntry($params);
		// file to be uploaded
		$uploadfile = $this->_upLoc . $hash.'.'.$params['extension'];
		
		if(! move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile))
		    throw new Exception("File was not uploaded!");
		
		$this->_newHashes = array($hash);
	}
	
	protected function _multiUpload(){
	}
	
	protected function _xhrUpload(){
	}

}
