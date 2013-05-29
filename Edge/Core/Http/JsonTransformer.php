<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 29/5/2013
 * Time: 2:07 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Http;


class JsonTransformer extends Transformer{

    public function decode($body){
        return json_decode($body, true);
    }

    public function encode($body){
        return json_encode($body);
    }
}