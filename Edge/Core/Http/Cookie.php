<?php

namespace Edge\Core\Http;
use Edge\Core\Edge,
    Edge\Core\Exceptions\EdgeException;

/**
 * Class Cookie
 * Wrapper class for cookie management
 * Supports cookie value hashing and validation
 * @package Edge\Core\Http
 */
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
            Edge::app()->logger->warn("Could not base64 decode cookie. Possible cookie tampering. Deleting it");
            $this->delete($name);
            return false;
        }
        list($value, $expiration, $hmac) = explode( '_', $decoded);
        if ($expiration < time()){
            Edge::app()->logger->warn("Cookie $name has expired. Deleting it");
            $this->delete($name);
            return false;
        }

        $hash = hash_hmac( 'sha1', $value . $expiration, $this->secret );

        if ($hmac != $hash ){
            Edge::app()->logger->crit("Cookie signature mismatch. Possible tampering. Deleting it");
            $this->delete($name);
            return false;
        }
        return $value;
    }

    public function delete($name){
        setcookie($name, "", time()-3600);
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
        return isset($_COOKIE[$name])?$_COOKIE[$name]:null;
    }
}