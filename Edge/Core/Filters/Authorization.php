<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Preprocess filter that applies access control
 * @package Edge\Core\Filters
 */
class Authorization extends BaseFilter{

    public function preProcess(Http\Response $response, Http\Request $request){
        $session = Edge::app()->session;
        if(!isset($session->user)){
            $response->redirect(Edge::app()->loginUrl);
        }
    }
}