<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Preprocess filter that checks for a CSRF token
 * @package Edge\Core\Filters
 */
class CsrfProtection extends BaseFilter{

    protected $tokenName = 'csrfToken';

    public function __construct(array $attrs){
        if(isset($attrs['tokenName'])){
            $this->tokenName = $attrs['tokenName'];
        }
        parent::__construct($attrs['applyTo']);
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        if(!$request->is('GET')){
            $tokenName = $this->tokenName;
            $body = $request->getParams();
            if(!isset($body[$tokenName])){
                $response->httpCode = 400;
                Edge::app()->logger->err("The body does not contain a CSRF token");
                $response->write();
            }
            if($body[$tokenName] != $request->getCsrfToken()){
                $response->httpCode = 400;
                Edge::app()->logger->err("The specified CSRF token is not valid");
                $response->write();
            }
            return true;
        }
    }
}