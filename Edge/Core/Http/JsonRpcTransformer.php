<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 29/5/2013
 * Time: 2:07 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Http;


class JsonRpcTransformer extends JsonTransformer{
    public $method;
    private $id;

    public function decode($body){
        $data = parent::decode($body);
        $this->method = $data['method'];
        $this->id = $data['id'];
        return $data['params'];
    }

    public function encode($body){
        $body = array(
            'jsonrpc' => '2.0',
            'result' => $body,
            'id' => $this->id
        );
        return parent::encode($body);
    }
}