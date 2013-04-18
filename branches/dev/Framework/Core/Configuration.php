<?php
namespace Framework\Core;

/**
 * Class responsible for loading configurations options
 */
class Configuration extends Singleton{

    private $settings = array();

    /**
    * Load framework configuration file by default.
    */
    protected function __construct(){
         $this->register(array(
             'name'=> 'Framework',
             'config' => __DIR__."/../Config/config.php"
         ));
     }

    /**
     * Override any stored settings with the supplied ones.
     * @param array $_settings
     */
    public function register(array $_settings){
        include($_settings['config']);
        $attrs = (array) $settings;
        $this->settings = array_merge($this->settings, $attrs);
    }

    public function getSettings(){
        return $this->settings;
    }
}
?>