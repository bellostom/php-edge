<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Preprocess filter that checks authorization
 * @package Edge\Core\Filters
 */
class Authorization extends BaseFilter{

    public function preProcess(Http\Response $response, Http\Request $request){
        if(Edge::app()->user()->isGuest()){
            $response->redirect(Edge::app()->loginUrl);
        }
    }
}