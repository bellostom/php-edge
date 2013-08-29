<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Base filter class
 * All Filter implementations should extend this class
 * The class handles whether the filter should be applied
 * for the specified action
 * @package Edge\Core\Filters
 */
abstract class BaseFilter implements \Edge\Core\Interfaces\Filter{

    /*
     * @var array
     * Array of methods for which the
     * filter should run
     * Default is to run the filter
     * for every method
     */
    private $applyTo;

    /**
     * @var array
     * Array of methods to exclude from applying
     * the filter
     */
    private $exceptions;

    /**
     * Pass an array with actions that filters should process
     * By default the filters will run for all actions
     * @param array $applyTo
     */
    public function __construct(array $attrs=array()){
        if(!isset($attrs['applyTo'])){
            $attrs['applyTo'] = ["*"];
        }
        if(!isset($attrs['exceptions'])){
            $attrs['exceptions'] = [];
        }
        $this->applyTo = $attrs["applyTo"];
        $this->exceptions = $attrs["exceptions"];
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        return true;
    }

    public function postProcess(Http\Response $response, Http\Request $request){
        return true;
    }

    /**
     * Check whether the filter needs to be applied to
     * the specified action
     * @param $action
     * @return bool
     */
    public function appliesTo($action){
        if(count($this->applyTo) == 1 && $this->applyTo[0] == "*" && !in_array($action, $this->exceptions)){
            return true;
        }
        return in_array($action, $this->applyTo) && !in_array($action, $this->exceptions);
    }
}