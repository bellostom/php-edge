<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Preprocess filter that checks authorization
 * @package Edge\Core\Filters
 */
class Authentication extends BaseFilter{

    protected $url;

    public function __construct(array $attrs){
        $this->url = $attrs['url'];
        parent::__construct();
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        if(Edge::app()->user()->isGuest()){
            Edge::app()->session->redirectUrl = $request->getRequestUrl();
            $response->redirect($this->url);
        }
    }
}