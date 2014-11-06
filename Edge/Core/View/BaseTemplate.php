<?php
namespace Edge\Core\View;

use Edge\Core\Edge;


class BaseTemplate {
    protected $tpl;
    protected $attrs = array();

    public function __construct($tpl){
        $this->tpl = $tpl;
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
            throw new \Exception("Template $this->tpl does not exist");
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
            include($this->tpl);
        }
        $parsed = ob_get_contents();
        ob_end_clean();
        return $parsed;
    }
} 