<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http,
    Edge\Core\Exceptions\Unauthorized;

/**
 * Preprocess filter that requires
 * a user to be authenticated before invoking the specified
 * action
 * @package Edge\Core\Filters
 */
class Authentication extends BaseFilter{

    protected $url;

    public function __construct(array $attrs){
        $this->url = $attrs['url'];
        parent::__construct($attrs);
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        if(Edge::app()->user()->isGuest()){
            if($request->isAjax()){
                throw new Unauthorized("Unauthorized access");
            }
            Edge::app()->session->redirectUrl = $request->getRequestUrl();
            $response->redirect($this->url);
        }
    }
}