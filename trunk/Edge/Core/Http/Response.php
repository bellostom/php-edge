<?php
namespace Edge\Core\Http;

use Edge\Core\Edge;

class Response{
	public $contentType = false;
	public $charset = 'UTF-8';
	public $body;
	public $httpCode = 200;
	protected static $httpCodes = array(
		200 => '200 OK',
		304 => '304 Not Modified',
		400 => '400 Bad Request',
		401 => '401 Authorization Required',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		500 => '500 Internal Server Error'
	);
	private $headers = array();

	public function isEtagValid($etag, $modified) {
		return ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
					@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $modified) &&
		    	(isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
		    		trim($_SERVER['HTTP_IF_NONE_MATCH']) == '"'.$etag.'"'));
	}

	public function expires($time) {
		$this->addHeader('Pragma', '');
		$this->addHeader('Cache-Control', '');
		$this->addHeader("Expires", gmdate('D, d M Y H:i:s', $time) . ' GMT');
	}

    public function lastModified($modified){
        $this->addHeader('Pragma', '');
        $this->addHeader('Cache-Control', '');
        $this->addHeader("Last-Modified", gmdate("D, d M Y H:i:s", $modified)." GMT");
    }

	public function setEtag($etag) {
		$this->addHeader('Pragma', '');
		$this->addHeader('Cache-Control', '');
		$this->addHeader('ETag', '"'.$etag.'"');
	}

	public function write()	{
		header("Pragma: no-cache");
		header("Cache-Control: no-cache,no-store");
		header("Expires:");
		header('HTTP/1.0 '. Response::$httpCodes[$this->httpCode]);
        $contentType = ($this->contentType)?$this->contentType:Edge::app()->request->getContentType();
		$contentType = sprintf("%s; charset=%s", $contentType, $this->charset);
		header('Content-Type: '.$contentType, true);
		foreach($this->headers as $key=>$val) {
			header("$key: $val", true);
		}
		echo $this->body;
		exit();
	}

	public function redirect($url) {
		header("Location: $url");
		exit();
	}

	public function addHeader($name, $value) {
		$this->headers[$name] = $value;
	}
}