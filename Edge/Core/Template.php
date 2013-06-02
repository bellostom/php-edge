<?php
namespace Edge\Core;

use Edge\Core\Exceptions\NotFound;

class Template{

    use TraitCachable;

	private $tpl;
	private $attrs = array();
    private $isCachable = false;

	public function __construct($tpl, array $cacheAttrs=array()){
		$this->tpl = $tpl;
        if(count($cacheAttrs) > 0){
            $this->init($cacheAttrs);
            $this->isCachable = true;
        }
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
        if($this->isCachable){
            $val = $this->get();
            if($val){
                return $val;
            }
            else{
                $val = $this->readTemplate();
                $this->set($val);
                return $val;
            }
        }
	    return $this->readTemplate();
	}

    protected function getExtraParams(){
        return $this->tpl;
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