<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 29/5/2013
 * Time: 2:07 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Http;


class XmlTransformer extends Transformer{

    public function decode($body){
        $xml = simplexml_load_string($body);
        return (array) $xml;
    }

    private function array2xml($array, $tag) {
        function ia2xml($array) {
            $xml="";
            foreach ($array as $key=>$value) {
                if (is_array($value)) {
                    $xml.="<$key>".ia2xml($value)."</$key>";
                } else {
                    $xml.="<$key>".$value."</$key>";
                }
            }
            return $xml;
        }
        return simplexml_load_string("<$tag>".ia2xml($array)."</$tag>");
    }

    public function encode($body){
        if(!is_array($body)){
            $body = array($body);
        }
        return $this->array2xml($body, "response")->asXml();
    }
}