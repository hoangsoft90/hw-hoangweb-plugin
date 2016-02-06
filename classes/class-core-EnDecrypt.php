<?php
/**
 * Class HW_Encryptor
 */
class HW_Encryptor
{
    /**
     * encryption key
     */
    const ENCRYPTION_KEY = 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282';
    /**
     * encrypt string
     * @param string $encrypt: string to encrypt
     * @param string $key: key
     */
    public static function encrypt($encrypt, $key = null){
        if(empty($key)) $key = self::ENCRYPTION_KEY;

        $encrypt = serialize($encrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $key = pack('H*', $key);
        $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
        $encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
        return $encoded;
    }

    /**
     * Decrypt Function
     * @param string $decrypt: encrypted string to be decrypt
     * @param string $key: key
     */
    public static function decrypt($decrypt, $key = null){
        if(empty($key)) $key = self::ENCRYPTION_KEY;

        $decrypt = explode('|', $decrypt.'|');
        $decoded = base64_decode($decrypt[0]);
        $iv = base64_decode($decrypt[1]);
        if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
        $key = pack('H*', $key);
        $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
        $mac = substr($decrypted, -64);
        $decrypted = substr($decrypted, 0, -64);
        $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
        if($calcmac!==$mac){ return false; }
        $decrypted = unserialize($decrypted);
        return $decrypted;
    }

    /**
     * detect string is base64 encoded
     * @param $str
     */
    public static function is_base64($str) {
        if ( base64_encode(base64_decode($str)) === $str){
            return true;
        }
        return false;
    }

    /**
     * @param $str
     * @return bool
     */
    public static function is_serialize_base64($str) {
        return self::is_serialized(base64_decode($str));
    }
    /**
     * check if string is serialized
     * @param $str
     * @return bool
     */
    public static function is_serialized($str) {
        $data = @unserialize($str);
        if ($data !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * encode with base64
     * @param $data
     * @return string
     */
    public static function encode64($data) {
        return base64_encode(serialize($data));
    }

    /**
     * decode with base64
     * @param $data
     * @return mixed
     */
    public static function decode64($data) {
        return unserialize(base64_decode($data));
    }
}