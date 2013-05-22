<?php
namespace Framework\Core;

/**
 * Class responsible for loading configurations options
 */
class Configuration{

    private $settings = array();

    /**
    * Load framework configuration file by default.
    */
    public function __construct(){
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