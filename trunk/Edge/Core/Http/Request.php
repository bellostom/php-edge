<?php
namespace Edge\Core\Http;

class Request {

    CONST RPC_MATCH = '/jsonrpc.+[\'"]method[\'"]\s*:\s*[\'"](.*?)[\'"]/';

    private $httpMethod;
    private $transformer;
    private $params = array();

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
            if(!$this->is('get')){
                $body = $this->getBody();
                if(preg_match(Request::RPC_MATCH, $body)){
                    $transformer = 'jsonrpc';
                }
            }
        }
        elseif(strstr($_SERVER['CONTENT_TYPE'], 'application/xml')){
            $transformer = 'xml';
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
}