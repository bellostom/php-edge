<?php
namespace Edge\Core\Interfaces;
use Edge\Core\Http;

interface Filter{

    public function preProcess(Http\Response $response, Http\Request $request);
    public function postProcess(Http\Response $response, Http\Request $request);
}
?>