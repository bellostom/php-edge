<?php
namespace Edge\Core\View;

use Edge\Core\Edge;

/**
 * Class BaseTemplate
 * Loads and parses a template file.
 * Use stream_resolve_include_path so that the include_path is searched
 * as well
 * @package Edge\Core\View
 */
class BaseTemplate {
    protected $tpl;
    protected $originalTpl;
    protected $attrs = [];

    public function __construct($tpl){
        $this->tpl = stream_resolve_include_path($tpl);
        $this->originalTpl = $tpl;
        $this->attrs['this'] = $this;
        if(isset(Edge::app()['i18n'])){
            $this->attrs['i18n'] = Edge::app()->i18n;
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
            throw new \Exception("Template $this->originalTpl does not exist");
        }
        return $this->readTemplate();
    }

    /**
     * Escape string coming from unknown sources
     * to prevent XSS
     * @param $str
     * @return string
     */
    public function escape($str){
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
    }

    protected function readTemplate(){
        extract($this->attrs);
        ob_start();
        if (is_file($this->tpl)){
            require($this->tpl);
        }
        $parsed = ob_get_contents();
        ob_end_clean();
        return $parsed;
    }
} 