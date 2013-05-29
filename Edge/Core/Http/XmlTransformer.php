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

    public function encode($body){
        if(!is_array($body)){
            $body = array($body);
        }
        $body = array_flip($body);
        $xml = new \SimpleXMLElement('<root/>');
        array_walk_recursive($body, array ($xml, 'addChild'));
        return $xml->asXML();
    }
}