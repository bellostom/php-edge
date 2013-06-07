<?php
namespace Edge\Utils;

class Thumbnail
{
	private $imgPath;
	private $imgResource;
	private $saveType = 'ImageJPEG';
	private $imageCreateFunc = 'ImageCreateFromJPEG';

	function __construct($imagePath)
	{
		$this->imgPath = $imagePath;
		$ext = substr(strrchr($this->imgPath, '.'), 1);
		$this->setImageFunctions($ext);
	}

	private function setImageFunctions($ext){
		switch ($ext){
			case 'jpeg':
				$this->imageCreateFunc = 'ImageCreateFromJPEG';
				$this->saveType = 'ImageJPEG';
				break;

			case 'png':
				$this->imageCreateFunc = 'ImageCreateFromPNG';
				$this->saveType = 'ImagePNG';
				break;

			case 'bmp':
				$this->imageCreateFunc = 'ImageCreateFromBMP';
				$this->saveType = 'ImageBMP';
				break;

			case 'gif':
				$this->imageCreateFunc = 'ImageCreateFromGIF';
				$this->saveType = 'ImageGIF';
				break;

			case 'vnd.wap.wbmp':
				$this->imageCreateFunc = 'ImageCreateFromWBMP';
				$this->saveType = 'ImageWBMP';
				break;

			case 'xbm':
				$this->imageCreateFunc = 'ImageCreateFromXBM';
				$this->saveType = 'ImageXBM';
				break;

			default:
				$image_create_func = 'ImageCreateFromJPEG';
				$this->saveType = 'ImageJPEG';
		}
	}

	public function resize($swidth, $sheight){
		$width  = $swidth;
		$height = $sheight;

		list($width_orig, $height_orig) = getimagesize($this->imgPath);

		//keep aspect ratio
		if ($width) {
			$factor = (float)$width / (float)$width_orig;
			$height = $factor * $height_orig;
		}
		else if ($height) {
			$factor = (float)$height / (float)$height_orig;
			$width = $factor * $width_orig;
		}

		$image_p = imagecreatetruecolor($width, $height);
		$createFunc = $this->imageCreateFunc;
		$image = $createFunc($this->imgPath);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		$this->imgResource = $image_p;
	}

	public function write($path = null)
	{
		$saveFunc = $this->saveType;
		$quality = 90;
		if($saveFunc == 'ImagePNG') {
			$quality = $quality/100;
		}
		if($path) {
			$saveFunc($this->imgResource, $path, $quality);
		}
		else {
			$saveFunc($this->imgResource);
		}
		imagedestroy($this->imgResource);
	}

	public function crop($width, $height)
	{
		list($width_orig, $height_orig) = getimagesize($this->imgPath);
		$createFunc =  $this->imageCreateFunc;
	    $myImage = $createFunc($this->imgPath);
	    if(!$myImage) {
	    	throw new EdgeException('Invalid JPEG image');
	    }
	    $ratio_orig = $width_orig/$height_orig;

	    if ($width/$height > $ratio_orig) {
	       $new_height = $width/$ratio_orig;
	       $new_width = $width;
	    } else {
	       $new_width = $height*$ratio_orig;
	       $new_height = $height;
	    }

	    $x_mid = $new_width/2;  //horizontal middle
	    $y_mid = $new_height/2; //vertical middle

	    $process = imagecreatetruecolor(round($new_width), round($new_height));

	    imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
	    $this->imgResource = imagecreatetruecolor($width, $height);
	    imagecopyresampled($this->imgResource, $process, 0, 0, ($x_mid-($width/2)), ($y_mid-($height/2)), $width, $height, $width, $height);

	    imagedestroy($process);
	    imagedestroy($myImage);
	}

	public function square($iDims)
	{
		$size = getimagesize($this->imgPath);
		$width = $size[0];
		$height = $size[1];
		if($width> $height)
		{
			$x = ceil(($width - $height) / 2 );
			$width = $height;
		}
		elseif($height> $width)
		{
			$y = ceil(($height - $width) / 2);
			$height = $width;
		}
		$new_im = imagecreatetruecolor($iDims, $iDims);
		$createFunc = $this->imageCreateFunc;
		$im = $createFunc($this->imgPath);
		imagecopyresampled($new_im,$im,0,0,$x,$y,$iDims,$iDims,$width,$height);
		$this->imgResource = $new_im;
	}
}
?>