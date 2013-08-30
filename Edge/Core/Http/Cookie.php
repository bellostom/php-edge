<?php

namespace Edge\Core\Http;
use Edge\Core\Edge;

/**
 * Class Cookie
 * Wrapper class for cookie management
 * Supports cookie value hashing and validation
 * @package Edge\Core\Http
 */
class Cookie {

    protected $sign = false;
    protected $secret = null;
    protected $httpOnly = false;
    protected $secure = false;
    protected $validated = [];

    public function __construct(array $attrs){
        $this->sign = $attrs['sign'];
        $this->secret = $attrs['secret'];
        $this->httpOnly = $attrs['httpOnly'];
        $this->secure = $attrs['secure'];
    }

    /**
     * Sign the cookie value to protect against
     * cookie tampering
     * @param $value
     * @param $expiration
     * @return string
     */
    protected function sign($value, $expiration){
        $hash = hash_hmac('sha1', $value, $this->secret);
        return base64_encode($value . '|_|' . $expiration . '|_|' . $hash);
    }

    /**
     * Validate signed cookie
     * @param $name
     * @return bool
     */
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
        list($value, $expiration, $hmac) = explode( '|_|', $decoded);
        if ($expiration < time()){
            Edge::app()->logger->warn("Cookie $name has expired. Deleting it");
            $this->delete($name);
            return false;
        }
        return $this->decodeCookie($hmac, $name, $value);
    }

    /**
     * Check cookie signature
     * @param $signature
     * @param $name
     * @param $value
     * @return bool
     */
    protected function decodeCookie($signature, $name, $value){
        $hash = hash_hmac('sha1', $value, $this->secret );
        if ($signature != $hash ){
            Edge::app()->logger->crit("Cookie signature mismatch. Possible tampering. Deleting it");
            $this->delete($name);
            return false;
        }
        return $value;
    }

    /**
     * Delete cookie
     * @param $name
     */
    public function delete($name){
        setcookie($name, "", time()-3600, "/", $this->secure, $this->httpOnly);
    }

    /**
     * Sign cookie value
     * @param $value
     * @param $expires
     * @return string
     */
    protected function getSignedValue($value, $expires){
        if($this->sign){
            $expiration = $expires;
            //an $expires value of 0 makes the cookie a session one
            //store a long expiration time
            if($expires == 0){
                $expiration = time() + 365 * 24 * 86400;
            }
            $value = $this->sign($value, $expiration);
        }
        return $value;
    }

    public function set($name, $value, $expires){
        $_COOKIE[$name] = $value;
        $value = $this->getSignedValue($value, $expires);
        setcookie($name, $value, $expires, "/", false, $this->secure, $this->httpOnly);
    }

    public function get($name){
        if($this->sign){
            if(!isset($this->validated[$name])){
                $this->validated[$name] = $this->validateCookie($name);
            }
            return $this->validated[$name];
        }
        return isset($_COOKIE[$name])?$_COOKIE[$name]:null;
    }
}