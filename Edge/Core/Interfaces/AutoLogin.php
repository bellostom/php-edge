<?php
namespace Edge\Core\Interfaces;

interface AutoLogin extends PreProcessFilter{
    public function checkCookieToken();
}
?>