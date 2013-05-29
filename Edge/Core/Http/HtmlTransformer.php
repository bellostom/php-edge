<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 29/5/2013
 * Time: 2:07 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Http;


class HtmlTransformer extends Transformer{

    public function decode($body){
        return $body;
    }

    public function encode($body){
        return $body;
    }
}