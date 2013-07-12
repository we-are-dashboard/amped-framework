<?php
class api_libs_Cropper
{
	public $tmpPath, $tmpFName, $params;
	public $imgInfo; /* 0=>width, 1=>height, 2=>type, 3=>attr, 4=>mime_type */
	
	/**
	 * Build the Cropper object.
	 * @param $tmpPath string The temporary location path of the file
	 * @param $tmpFName string The temporary name of the file
	 */
	public function __construct($tmpPath, $tmpFName, $params)
	{
		$this->tmpPath = $tmpPath;
		$this->tmpFName = $tmpFName;
		$this->params = $params;
		
		if( !($this->imgInfo = getimagesize($tmpPath.$tmpFName)) )
			throw new Exception("The file is not valid or is not an image!");
	}
	
	/**
	 * @todo add check against the template
	 */
	public function copyAndCrop($destPath, $fileName, $fileExtension, $existingWidth, $existingHeight,
		$requiredWidth, $requiredHeight, $newWidth, $newHeight, $x, $y)
	{
		$newFile = $destPath.$fileName . '.' . $fileExtension;
		$existingFile = $this->tmpPath.$this->tmpFName;
		
		$requiredRatio = $requiredWidth / $requiredHeight;
		$newRatio = $newWidth / $newHeight;
		if(-0.05 > ($requiredRatio - $newRatio) && ($requiredRatio - $newRatio) > 0.05)
			throw new Exception("Try Cropping again. Ratios don't correspond.", '1320');
		
		$image_given = imagecreatetruecolor($newWidth, $newHeight);
		$image_req = imagecreatetruecolor($requiredWidth, $requiredHeight);
		
		$image = $this->_readSource($existingFile);
			
		if(! imagecopyresampled($image_given, $image, 0, 0, $x, $y,
				$existingWidth, $existingHeight, $existingWidth, $existingHeight))
			throw new Exception('The image could not be resampled.','1330');
			
		if($image_req) {
			if(! imagecopyresampled($image_req, $image_given, 0, 0, 0, 0, $requiredWidth,
					$requiredHeight, $newWidth, $newHeight))
				throw new Exception('The image could not be resampled.','1331');
		}
		
		$image_final = ($image_req) ? $image_req : $image_given;
		
		if(! imagejpeg($image_final, $newFile, $this->params['q']))
			throw new Exception('The image could not be saved', '1340');
	}
	
	/**
	 * @todo add check against the template
	 */
	public function copyAndResize($nfPath,$maxWidth,$maxHeight)
	{
		$maxWidth = ($maxWidth) ? $maxWidth : 999999;
		$maxHeight = ($maxHeight) ? $maxHeight : 999999;
		
		$newFile = $nfPath;
		$existingFile = $this->tmpPath.$this->tmpFName;

		$rSourceImage = $this->_readSource($existingFile);
		
    	if($this->params['c'] == 'false'){
    		list($maxWidth, $maxHeight, $iNewWidth, $iNewHeight) = $this->_computeRatios($maxWidth, $maxHeight);
    		$rDestinationImage = $this->CRimageResampleMax($maxWidth,$maxHeight,$iNewWidth,$iNewHeight,$rSourceImage);
    	}else
	    	$rDestinationImage = $this->_centerResampleImage($maxWidth, $maxHeight, $rSourceImage);
    	
    	if(!imagejpeg($rDestinationImage, $newFile, $this->params['q']))
    		throw new Exception("Error saving resized image: " . $newFile, 99);

    	imagedestroy($rDestinationImage);
    	imagedestroy($rSourceImage);
	
	}
	
	private function _computeRatios($maxWidth, $maxHeight){
	 	$iNewWidth = $iNewHeight =	0;

	    if($this->params['a'] == 'false'){
	    	$iNewWidth = $maxWidth;
	        $iNewHeight	= $maxHeight;
	    }elseif ($this->imgInfo[0] > $maxWidth || $this->imgInfo[1] > $maxHeight){
	    	// Will need to resize
			if ((($this->imgInfo[0] - $maxWidth) >= ($this->imgInfo[1] - $maxHeight)) && 
					((intval(($maxWidth / $this->imgInfo[0] ) * $this->imgInfo[1])) <= $maxHeight))
	        {
	            $iNewWidth	=	$maxWidth;
	            $iNewHeight	=	intval(($maxWidth / $this->imgInfo[0] ) * $this->imgInfo[1]);
	            if($maxHeight==999999) $maxHeight = $iNewHeight;
	        }else{
	            $iNewHeight	=	$maxHeight;
	            $iNewWidth	=	intval(($iNewHeight / $this->imgInfo[1]) * $this->imgInfo[0]);
	            if(!$maxWidth==999999) $maxWidth = $iNewWidth;
	        }
	    }else{
	    	// No need resize
	        $iNewWidth = $maxWidth = $this->imgInfo[0];
	        $iNewHeight	= $maxHeight = $this->imgInfo[1];
	    }
	    
	    return array($maxWidth, $maxHeight, $iNewWidth, $iNewHeight);
	}
	
	private function _centerResampleImage($maxWidth, $maxHeight, $rSourceImage){
		$iNewWidth = $iNewHeight =	$srcX = $srcY = 0;
		if ((($this->imgInfo[0] - $maxWidth) >= ($this->imgInfo[1] - $maxHeight)) && 
					((intval(($maxWidth / $this->imgInfo[0] ) * $this->imgInfo[1])) <= $maxHeight))
	        {
	            $iNewHeight	=	$this->imgInfo[1];
	            $iNewWidth	=	intval((($this->imgInfo[1] - $maxHeight) / 
	            	$this->imgInfo[1]) * $this->imgInfo[0]) + $maxWidth;
	            $srcX = ($this->imgInfo[0] - $iNewWidth) / 2;
	        }else{
	            $iNewWidth	=	$this->imgInfo[0];
	            $iNewHeight	=	intval((($this->imgInfo[0] - $maxWidth) / 
	            	$this->imgInfo[0]) * $this->imgInfo[1]) + $maxHeight;
	            $srcY = ($this->imgInfo[1] - $iNewHeight) / 2;
	        }
   		
		$rDestinationImage  = imagecreatetruecolor($maxWidth, $maxHeight);
	    imagecopyresampled($rDestinationImage, $rSourceImage, 0, 0, $srcX, $srcY,
	    	$maxWidth, $maxHeight, $iNewWidth, $iNewHeight);
   		
   		return $rDestinationImage;
	}
	
	private function _readSource($existingFile)
	{
    	// Read source file based on file type
    	switch ($this->imgInfo[2]){
    		case IMAGETYPE_GIF:
    			return imagecreatefromgif($existingFile);
    		case IMAGETYPE_JPEG:
    			return imagecreatefromjpeg($existingFile);
    		case IMAGETYPE_PNG:
    			return imagecreatefrompng($existingFile);
    		default:
    			throw new Exception("Unsupported image format: " . $this->imgInfo[2]);
    	}    	
	}
	
	private function CRimageResampleMax($maxWidth,$maxHeight,$iNewWidth,$iNewHeight,$rSourceImage)
	{
		$dstX = ($maxWidth - $iNewWidth) / 2;
   		$dstY = ($maxHeight - $iNewHeight) / 2;
   		$rDestinationImage  = imagecreatetruecolor($maxWidth, $maxHeight);
   		$rgb = $this->_hexToRGB($this->params['b']);
   		$imageColor = imagecolorallocate($rDestinationImage,$rgb['red'], $rgb['green'], $rgb['blue']);
   		imagefill($rDestinationImage,0,0,$imageColor);
   		imagecopyresampled($rDestinationImage, $rSourceImage, $dstX, $dstY, 0, 0, $iNewWidth, $iNewHeight, 
   			$this->imgInfo[0], $this->imgInfo[1]);
   		
   		return $rDestinationImage;
	}
	
	private function _hexToRGB($colour){
		$colour = trim($colour, '#');
		
        if ( strlen( $colour ) == 6 )
                list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        elseif(strlen( $colour ) == 3)
                list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
        else
        	return false;
        	
        $r = hexdec( $r );
        $g = hexdec( $g );
        $b = hexdec( $b );
        
        return array( 'red' => $r, 'green' => $g, 'blue' => $b );
	}
	
	public function copyNoResize($nfPath)
	{
		$newFile = $nfPath;
		$existingFile = $this->tmpPath.$this->tmpFName;
		
		if(!copy($existingFile, $newFile))
	    	throw new Exception("File could not be copied !");
	}
	
	public static function copy($tmpFPath, $nfPath)
	{
		$newFile = $nfPath;
		$existingFile = $tmpFPath;

	    if(!copy($existingFile, $newFile))
	    	throw new Exception("File could not be copied !");
	}
}
?>