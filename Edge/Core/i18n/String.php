<?php
namespace Edge\Core\i18n;
/**
 * Class String
 * Access localized strings either as array or object
 * or object
 * @package Edge\Core\i18n
 */
class String implements \ArrayAccess{

    protected $strings;

    public function __construct(array $strings){
        $this->strings = $strings;
    }

    public function __get($name){
        return $this->strings[$name];
    }

    public function offsetExists($offset){
        return array_key_exists($offset, $this->strings);
    }

    public function offsetGet($offset){
        return $this->strings[$offset];
    }

    public function offsetSet($offset, $value){
        throw new \Exception("Read only list");
    }

    public function offsetUnset($offset){
        throw new \Exception("Read only list");
    }
}