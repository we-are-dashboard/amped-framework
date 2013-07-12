<?php
class api_libs_InfoPic{
	//@todo replace from ... 'txt'=>'text/plain' ->to-> 'text/plain'=>'txt'
	public static $_mimeTypes = array(
		'txt'=>'text/plain', 'htm'=>'text/html', 'html'=>'text/html', 
		'php'=>'text/html', 'css'=>'text/css', 'js'=>'application/javascript', 
		'json'=>'application/json', 'xml'=>'application/xml', 'swf'=>'application/x-shockwave-flash', 
		'flv'=>'video/x-flv', 

		// images
		'png'=>'image/png', 'jpe'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'jpg'=>'image/jpeg', 
		'gif'=>'image/gif', 'bmp'=>'image/bmp', 'ico'=>'image/vnd.microsoft.icon', 'tiff'=>'image/tiff', 
		'tif'=>'image/tiff', 'svg'=>'image/svg+xml', 'svgz'=>'image/svg+xml', 
	
		// archives
		'zip'=>'application/zip', 'rar'=>'application/x-rar-compressed', 'exe'=>'application/x-msdownload', 
		'msi'=>'application/x-msdownload', 'cab'=>'application/vnd.ms-cab-compressed', 
	
		// audio/video
		'mp3'=>'audio/mpeg', 'qt'=>'video/quicktime', 'mov'=>'video/quicktime', 
	
		// adobe
		'pdf'=>'application/pdf', 'psd'=>'image/vnd.adobe.photoshop', 'ai'=>'application/postscript', 
		'eps'=>'application/postscript', 'ps'=>'application/postscript', 
	
		// ms office
		'doc'=>'application/msword', 'rtf'=>'application/rtf', 'xls'=>'application/vnd.ms-excel', 
		'ppt'=>'application/vnd.ms-powerpoint', 
	
		// open office
		'odt'=>'application/vnd.oasis.opendocument.text', 'ods'=>'application/vnd.oasis.opendocument.spreadsheet');
	protected $_file, $_MTs, $_info;
	
	/** @param String $file The full file path (+extension) */
	public function __construct($file){
		$this->_file = $file;
		$this->_MTs = self::$_mimeTypes;
		if( !($this->_info = getimagesize($file)) )
			throw new Exception("The file is not valid or is not an image!");
	}
	
	public function getW(){
		return $this->_info[0];
	}
	
	public function getH(){
		return $this->_info[1];
	}
	
	public function getMime(){
		$ext = strtolower(array_pop(explode('.', $this->_file)));
		if(array_key_exists($ext, $this->_MTs)){
			return $this->_MTs[$ext];
		}elseif(function_exists('finfo_open')){
			/*$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $this->_file);
			finfo_close($finfo);*/
			return $this->_info['mime'];
		}else{
			return 'application/octet-stream';
		}
	}
	
	public function getExt(){
		$ext = strtolower(array_pop(explode('.', $this->_file)));
		if(array_key_exists($ext, $this->_MTs)){
			return $ext;
		}else{
			return '';
		}
	}
	
	/** Check that the passed file passed is a supported image type
	 * @return bool True if it is, otherwise false */
	public function isSupportedImage(){
		$type = self::getType($this->_file);
		return self::isSupportedImageMime($type);
	}
	
	/** Check that the passed mime type is a supported image type
	 * @param $mimeType string The mime type to be checked
	 * @return bool True if it is, otherwise false */
	public function isSupportedImageMime($mimeType){
		switch($mimeType){
			case 'image/png' :
			case 'image/jpeg' :
			case 'image/gif' :
				break;
			
			default :
				return false;
		}
		return true;
	}

}




