<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Base filter class
 * @package Edge\Core\Filters
 */
abstract class BaseFilter implements \Edge\Core\Interfaces\Filter{

    private $applyTo;

    public function __construct(array $applyTo=array("*")){
        $this->applyTo = $applyTo;
    }

    /**
     * Check whether the filter needs to be applied to
     * the specified action
     * @param $action
     * @return bool
     */
    public function appliesTo($action){
        if($this->applyTo[0] == "*"){
            return true;
        }
        return in_array($action, $this->applyTo);
    }
}