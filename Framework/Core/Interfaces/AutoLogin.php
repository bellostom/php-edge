<?php
namespace Framework\Core\Interfaces;

interface AutoLogin extends PreProcessFilter{
    public function checkCookieToken();
}
?>