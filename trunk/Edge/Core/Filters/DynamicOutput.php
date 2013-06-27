<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Class DynamicOutput
 * This filter is defined by default in the BaseController.
 * Its purpose is to add dynamic logic to cached templates.
 * It searches for tokens defined as {{callback}} and replaces
 * the token by executing the callback function
 * @package Edge\Core\Filters
 */
class DynamicOutput extends BaseFilter{

    /**
     * After the request has been processed, get the response
     * body and replace any dynamic placeholders defined
     * @param Http\Response $response
     * @param Http\Request $request
     */
    public function postProcess(Http\Response $response, Http\Request $request){
        if(is_string($response->body)){
            $response->body = preg_replace_callback("/{{(.+)}}/", function($matches){
                return call_user_func($matches[1]);
            }, $response->body);
        }
    }
}