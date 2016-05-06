<?php
namespace Edge\Tests\Core\Http;

class XmlPostRequestTest extends RequestTestCase{

    protected function setDefaults(){
        $_SERVER['REQUEST_URI'] = '/some/url';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = "application/xml";
    }

    protected function getTransformer(){
        return 'Edge\Core\Http\XmlTransformer';
    }

    protected function getRequestBody(){
        return '<?xml version="1.0"?><CATALOG><CD><TITLE>EmpireBurlesque</TITLE><ARTIST>BobDylan</ARTIST><COUNTRY>USA</COUNTRY><COMPANY>Columbia</COMPANY><PRICE>10.90</PRICE><YEAR>1985</YEAR></CD><CD><TITLE>Stillgottheblues</TITLE><ARTIST>GaryMoore</ARTIST><COUNTRY>UK</COUNTRY><COMPANY>Virginrecords</COMPANY><PRICE>10.20</PRICE><YEAR>1990</YEAR></CD></CATALOG>';
    }

}