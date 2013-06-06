<?php

namespace Edge\Core\Http;


class Cookie {

    protected $encrypt = false;
    protected $secret = null;
    protected $httpOnly = false;
    protected $secure = false;

    public function __construct(array $attrs){
        $this->encrypt = $attrs['encrypt'];
        $this->secret = $attrs['secret'];
        $this->httpOnly = $attrs['httpOnly'];
        $this->secure = $attrs['secure'];
    }

    protected function sign($value, $expiration){
        $hash = hash_hmac( 'sha1', $value . $expiration, $this->secret );
        return base64_encode($value . '_' . $expiration . '_' . $hash);
    }

    protected function validateCookie($name){
        if (empty($_COOKIE[$name]) ){
            return false;
        }

        $decoded = base64_decode($_COOKIE[$name]);
        if($decoded === false){
            return false;
        }
        list($value, $expiration, $hmac) = explode( '_', $decoded);
        if ($expiration < time()){
            //deleteCookie();
            //error_log('Cookie expired');
            return false;
        }

        $hash = hash_hmac( 'sha1', $value . $expiration, $this->secret );

        if ($hmac != $hash ){
            //deleteCookie();
            //error_log('Invalid signature');
            return false;
        }
        return $value;
    }

    public function set($name, $value, $expires){
        if($this->encrypt){
            $value = $this->sign($value, $expires);
        }
        setcookie($name, $value, $expires, "/", false, $this->secure, $this->httpOnly);
    }

    public function get($name){
        if($this->encrypt){
            return $this->validateCookie($name);
        }
        return $_COOKIE[$name];
    }
}