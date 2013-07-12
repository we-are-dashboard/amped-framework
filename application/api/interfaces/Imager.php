<?php
class Imager extends api_service_SInterface
{	
	private $_file, $validator, $_originalSize;
	private $params = array(
		"url" => NULL,
		"valid_image" => false,
		"w" => "0",
		"h" => "0",
		"s" => "0",
		"q" => "100",
		"a" => "true",
		"b" => "#ffffff",
		"c" => "false",
		"x" => "0",
		"y" => "0",
		"r" => 'image'
	);

	
	public function echoOutput(){
		if($this->params['r'] == 'image'){
			header("Content-type: image/jpeg");
			readfile($this->_file);
		}elseif($this->params['r'] == 'json'){
			api_service_Feedback::getInstance()->setResponse($this->params);
			echo api_service_Feedback::getInstance()->getJsonFeedback();
		}
		
	}
	
	public function run(){
		/* @todo , x and y could allow for default of 0, finish width or height only (look at the -1 */
		
		$model = new api_models_Imager(api_service_DB::get());
		$this->validator = new api_libs_ImageValidator();
		
		$params = $model->getImage();
		if(false){//@todo put this back in place $params){
			$this->_file = ROOT_PATH."/images/uploads/".$params['hash'].".jpg";
			$this->_service->setSuccess(true);
			$this->echoOutput();
			return;
		}
			
		$params = $model->buildNewEntry($this->_stripImageParams());
		$this->validator->validate('all', $params);
		$this->params['hash'] = $params['hash'];
		$this->params['short_url'] = Config::get('API_URL').'/'.$params['hash'];
		
		//$img = "http://www.flashbynature.com/images/about.jpg";
		$img = $params['url'];
		$w = $params['w'];
		$h = $params['h'];
		
		$this->saveImage($img, ROOT_PATH."/images/tmp/".$params['hash'].".jpg");
		$cropper = new api_libs_Cropper(ROOT_PATH."/images/tmp/", $params['hash'].".jpg", $params);

		if($params['x'] != '0' || $params['y'] != '0')
			$cropper->copyAndCrop(ROOT_PATH."/images/uploads/", $params['hash'],
				'jpg', $this->_originalSize[0], $this->_originalSize[1], $params['w'], 
				$params['h'], $params['w'], $params['h'], $params['x'], $params['y']);
		else
			$cropper->copyAndResize(ROOT_PATH."/images/uploads/".$params['hash'].".jpg", $w, $h);
		
		$this->_file = ROOT_PATH."/images/uploads/".$params['hash'].".jpg";
		$this->_service->setSuccess(true);
		$this->echoOutput();
	}
	
	//@TODO check if last character is a "/" if not add it
	/** Strip all image parameters as per the API, they'll have to go in the db */
	private function _stripImageParams(){
		
		$r = substr($_SERVER['REQUEST_URI'], -1) == '/' ? $_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'] . '/';
		//if($r == '/') return;
		//else $this->params['noURI'] = false;
		
		$this->params['request_uri'] = $r;
		$r = substr($r, strpos($r, '/', 2));
		
		$len = strlen($r)-1;
		$r = substr($r, 1, $len);
		$i = 0;
		while( $i < $len-1 ){
			if ( $r{$i} == '/' && $i+2 < $len && $r{$i+2} == ":"){
				if ( $this->params['url'] == NULL)
					$this->params['url'] = substr($r,0,$i);
				//$v is for value
				if ( isset($this->validator->params[$r{$i+1}]) ){
					$v = $i+3;
					$val = '';
					while($r{$v} != "/"){
						$val .= $r{$v};
						$v++;
					}
					$this->params[$r{$i+1}] = $val;
					$i = $v;
				} else {
					//var_dump("fail");
					$i++;
				}
			} else {
				$i++;
			}
		}
		
		// if there was no '/{char}:{val} occurrence so $r should be the location even if invalid
		if(!$this->params['url'])
			$this->params['url'] = trim($r, '/');
		
		$this->adjustDefaultParams();
		
		return $this->params;
	}
	
	protected function adjustDefaultParams(){
		$defaultSize = $this->_originalSize = getimagesize($this->params['url']);
		if(!$defaultSize) 
			throw new Exception("The image at: '{$this->params['url']}' does not exist or is not a valid image.");
			
		$this->params['valid_image'] = true;
		
		if($this->params['s']){
			$vals = explode('x', $this->params['s']);
			$this->params['w'] = $vals[0];
			$this->params['h'] = $vals[1];
		}elseif($this->params['w'] == '0' && $this->params['h'] == '0'){
			$this->params['w'] = $defaultSize[0];
			$this->params['h'] = $defaultSize[1];
		}
	}
	
	public function getParams(){
		return $this->params;
	}
	
	// Image Saving Using cURL 
    protected function saveImage($img, $fullpath){
        $ch = curl_init ($img);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        //curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1); 
        
        $rawdata=curl_exec($ch);
        curl_close ($ch);
        
        if(file_exists($fullpath)){
            unlink($fullpath);
        }
		
        $fp = fopen($fullpath,'x');
        fwrite($fp, $rawdata);
        fclose($fp);
    }

	

}
