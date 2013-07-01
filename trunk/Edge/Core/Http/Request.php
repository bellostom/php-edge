<?php
namespace Edge\Core\Http;

use Edge\Core\Edge;

class Request {

    CONST RPC_MATCH = '/jsonrpc.+[\'"]method[\'"]\s*:\s*[\'"](.*?)[\'"]/';

    private $httpMethod;
    private $transformer;
    private $params = array();
    private $contentType = "text/html";

    public function __construct(){
        $this->httpMethod = $_SERVER['REQUEST_METHOD'];
        if(!isset($_SERVER['CONTENT_TYPE'])){
            $_SERVER['CONTENT_TYPE'] = "text/html";
        }
        $this->init();
    }

    private function init(){
        if(strstr($_SERVER['CONTENT_TYPE'], 'application/json')){
            $transformer = 'json';
            $this->contentType = "application/json";
            if(!$this->is('get')){
                $body = $this->getBody();
                if(preg_match(Request::RPC_MATCH, $body)){
                    $transformer = 'jsonrpc';
                }
            }
        }
        elseif(strstr($_SERVER['CONTENT_TYPE'], 'application/xml')){
            $transformer = 'xml';
            $this->contentType = "application/xml";
        }
        else{
            $transformer = 'html';
        }
        $this->transformer = Transformer::factory($transformer);
        if(!$this->is('get')){
            $data = $this->getBody();
            $this->params = $this->transformer->decode($data);
        }
    }

    public function setCookie($name, $value, $expires){
        Edge::app()->cookie->set($name, $value, $expires);
    }

    public function getCookie($name){
        return Edge::app()->cookie->get($name);
    }

    public function deleteCookie($name){
        return Edge::app()->cookie->delete($name);
    }

    public function getRequestUrl(){
        return $_SERVER['PATH_INFO'];
    }

    public function getContentType(){
        return $this->contentType;
    }

    public function is($method){
        return strtolower($this->httpMethod) == strtolower($method);
    }

    public function isJsonRpc(){
        return $this->transformer instanceof JsonRpcTransformer;
    }

    public function getParams(){
        return $this->params;
    }

    public function getTransformer(){
        return $this->transformer;
    }

    public function getBody(){
        static $body;
        if($body === null){
            if($this->transformer instanceof HtmlTransformer){
                $body = $_POST;
            }else{
                $body = file_get_contents('php://input');
            }
        }
        return $body;
    }

    public function getHttpMethod(){
        return $this->httpMethod;
    }
}