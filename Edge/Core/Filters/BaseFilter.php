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

    private $applyTo;
    protected $preProcessOrder = 10;
    protected $postProcessOrder = 10;

    /**
     * Pass an array with actions that filters should process
     * By default the filters will run for all actions
     * @param array $applyTo
     */
    public function __construct(array $applyTo=array("*")){
        $this->applyTo = $applyTo;
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        return true;
    }

    public function postProcess(Http\Response $response, Http\Request $request){
        return true;
    }

    public function getpreProcessOrder(){
        return $this->preProcessOrder;
    }

    public function getpostProcessOrder(){
        return $this->postProcessOrder;
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