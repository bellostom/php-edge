<?php
namespace Edge\Utils;

class Thumbnail{
    protected $imgPath;
    protected $imgResource;
    protected $saveType = 'ImageJPEG';
    protected $imageCreateFunc = 'ImageCreateFromJPEG';
    protected $contentType;
    protected static $quality = 90;

    function __construct($imagePath){
        $this->imgPath = $imagePath;
        $ext = static::getExtension($imagePath);
        $this->setImageFunctions($ext);
    }

    /**
     * returns the extension of the $imagePath
     * @param $imagePath
     *
     * @return string
     */
    public static function getExtension($imagePath){
        return substr(strrchr($imagePath, '.'), 1);
    }

    /**
     * sets the image handling function, content type and
     * save type according to the provided extension
     *
     * @param $ext
     */
    protected function setImageFunctions($ext){
        switch($ext){
            case 'jpg':
            case 'jpeg':
                $this->imageCreateFunc = 'ImageCreateFromJPEG';
                $this->saveType = 'ImageJPEG';
                $this->contentType = 'image/jpeg';
                break;

            case 'png':
                $this->imageCreateFunc = 'ImageCreateFromPNG';
                $this->saveType = 'ImagePNG';
                $this->contentType = 'image/png';
                break;

            case 'bmp':
                $this->imageCreateFunc = 'ImageCreateFromBMP';
                $this->saveType = 'ImageBMP';
                $this->contentType = 'image/bmp';
                break;

            case 'gif':
                $this->imageCreateFunc = 'ImageCreateFromGIF';
                $this->saveType = 'ImageGIF';
                $this->contentType = 'image/gif';
                break;

            case 'vnd.wap.wbmp':
                $this->imageCreateFunc = 'ImageCreateFromWBMP';
                $this->saveType = 'ImageWBMP';
                $this->contentType = 'image/vnd.wap.wbmp';
                break;

            case 'xbm':
                $this->imageCreateFunc = 'ImageCreateFromXBM';
                $this->saveType = 'ImageXBM';
                $this->contentType = 'image/x-xbitmap';
                break;

            default:
                $this->imageCreateFunc = 'ImageCreateFromJPEG';
                $this->saveType = 'ImageJPEG';
                $this->contentType = 'image/jpeg';
        }
    }

    /**
     * returns the content type of the provided
     * image
     *
     * @return mixed
     */
    public function getContentType(){
        return $this->contentType;
    }

    /**
     * resizes the provided image to the specified
     * width/height
     *
     * @param $swidth
     * @param $sheight
     */
    public function resize($swidth, $sheight){
        $width = $swidth;
        $height = $sheight;

        list($width_orig, $height_orig) = getimagesize($this->imgPath);

        //keep aspect ratio
        if($width){
            $factor = (float)$width / (float)$width_orig;
            $height = $factor * $height_orig;
        }else{
            if($height){
                $factor = (float)$height / (float)$height_orig;
                $width = $factor * $width_orig;
            }
        }
        $this->imgResource = $this->createImage($width, $height);
        $createFunc = $this->imageCreateFunc;
        $image = $createFunc($this->imgPath);
        imagecopyresampled($this->imgResource, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        imagedestroy($image);
    }

    /**
     * saves the image if no path is provided
     * it flushes it
     *
     * @param null $path
     */
    public function write($path = null){
        $saveFunc = $this->saveType;
        $quality = static::$quality;
        if($saveFunc == 'ImagePNG'){
            $quality = $quality / 100;
        }
        if($path){
            $saveFunc($this->imgResource, $path, $quality);
        }else{
            $saveFunc($this->imgResource);
        }
        imagedestroy($this->imgResource);
    }

    /**
     * when the image is of png type try to preserve transparency
     *
     * @param $width
     * @param $height
     *
     * @return resource
     */
    protected function createImage($width, $height){
        $image = imagecreatetruecolor($width, $height);
        if($this->contentType == "image/png"){
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $trans_layer_overlay = imagecolorallocatealpha($image, 220, 220, 220, 127);
            imagefill($image, 0, 0, $trans_layer_overlay);
        }
        return $image;
    }

    /**
     * crops the provided image to the specified
     * width/height
     *
     * @param $width
     * @param $height
     *
     * @throws EdgeException
     */
    public function crop($width, $height){
        list($width_orig, $height_orig) = getimagesize($this->imgPath);
        $createFunc = $this->imageCreateFunc;
        $myImage = $createFunc($this->imgPath);
        if(!$myImage){
            throw new EdgeException('Invalid JPEG image');
        }
        $ratio_orig = $width_orig / $height_orig;

        if($width / $height > $ratio_orig){
            $new_height = $width / $ratio_orig;
            $new_width = $width;
        }else{
            $new_width = $height * $ratio_orig;
            $new_height = $height;
        }

        $x_mid = $new_width / 2;  //horizontal middle
        $y_mid = $new_height / 2; //vertical middle

        $process = $this->createImage(round($new_width), round($new_height));

        imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        $this->imgResource = $this->createImage($width, $height);
        imagecopyresampled($this->imgResource, $process, 0, 0, ($x_mid - ($width / 2)), ($y_mid - ($height / 2)), $width, $height, $width, $height);
        imagedestroy($process);
        imagedestroy($myImage);
    }

    public function square($iDims){
        $size = getimagesize($this->imgPath);
        $width = $size[0];
        $height = $size[1];
        if($width > $height){
            $x = ceil(($width - $height) / 2);
            $width = $height;
        }elseif($height > $width){
            $y = ceil(($height - $width) / 2);
            $height = $width;
        }
        $new_im = imagecreatetruecolor($iDims, $iDims);
        $createFunc = $this->imageCreateFunc;
        $im = $createFunc($this->imgPath);
        imagecopyresampled($new_im, $im, 0, 0, $x, $y, $iDims, $iDims, $width, $height);
        $this->imgResource = $new_im;
    }
}