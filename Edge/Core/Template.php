<?php
namespace Edge\Core;

use Edge\Core\Exceptions\NotFound;

class Template{
	private $tpl;
	private $attrs = array();

	public function __construct($tpl){
		$this->tpl = $tpl;
	}

	public function __set($member, $value){
		$this->attrs[$member] = $value;
	}

	public function __get($key){
		return $this->attrs[$key];
	}

	public function parse(){
	    if(!file_exists($this->tpl)){
	    	throw new NotFound("Template $this->tpl does not exist");
	    }
	    return $this->readTemplate();
	}

	private function readTemplate(){
		extract($this->attrs);
	    ob_start();
	    if (is_file($this->tpl)){
	        include($this->tpl);
        }
	    $parsed = ob_get_contents();
	    ob_end_clean();
	    return $parsed;
	}
}
?>