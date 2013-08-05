<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Exceptions\BadRequest,
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
        parent::__construct($attrs);
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        if(!$request->is('GET')){
            $tokenName = $this->tokenName;
            $body = $request->getParams();
            if(!isset($body[$tokenName])){
                throw new BadRequest("The body does not contain a CSRF token");
            }
            if($body[$tokenName] != $request->getCsrfToken()){
                throw new BadRequest("The specified CSRF token is not valid");
            }
            return true;
        }
    }
}