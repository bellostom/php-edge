<?php

namespace Edge\Core\Http;
use Edge\Core\Edge;

/**
 * Class EncryptedCookie
 * Encrypt cookie value
 * @package Edge\Core\Http
 */
class EncryptedCookie extends Cookie{

    private $encryptionKey;
    private static $encryptionMethod = "AES-256-CBC";

    public function __construct(array $attrs){
        $attrs['sign'] = true;
        $this->encryptionKey = $attrs['encryptionKey'];
        parent::__construct($attrs);
    }

    /**
     * Encrypt value and prepend the vector key
     * to be used when decrypting the message
     * @param $value
     * @return string
     */
    private function encrypt($value){
        $encryptionMethod = static::$encryptionMethod;
        $secretHash = $this->encryptionKey;
        $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        return base64_encode($iv . openssl_encrypt($value, $encryptionMethod, $secretHash, 0, $iv));
    }

    /**
     * Decrypt value. Get the vector key
     * from the beginning of the value
     * and use it to decrypt
     * @param $encryptedMessage
     * @return string
     */
    private function decrypt($encryptedMessage){
        $encryptionMethod = static::$encryptionMethod;
        $secretHash = $this->encryptionKey;
        $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
        $encryptedMessage = base64_decode($encryptedMessage);
        $iv = substr($encryptedMessage, 0, $ivSize);
        return openssl_decrypt(substr($encryptedMessage, $ivSize), $encryptionMethod, $secretHash, 0, $iv);
    }

    protected function decodeCookie($signature, $name, $value){
        $value = parent::decodeCookie($signature, $name, $value);
        return $this->decrypt($value);
    }

    protected function sign($value, $expiration){
        $value = $this->encrypt($value);
        return parent::sign($value, $expiration);
    }
}