<?php
/**
 * may need openssl
 * User: Administrator
 * Date: 2019/5/14
 * Time: 14:46
 */

namespace App\Library;

/**
 * Class JwtToken
 * @package App\Library
 * @uses
 *    1、\App\Library\JwtToken::getInstance($scret)->setCustom(['user_id'=>47230,'user_name'=>'test_group'])->setExpireAt( time() + 864000 )->gen()
 *    2、\App\Library\JwtToken::getInstance($scret)->verify( $jwt_string )
 *    3、
 */
class JwtToken {

    private $_secret = '';
    private $_algorithm = 'SHA256';
    private $_encrypted = false;

    private $_payload = [];
    private $_jwt = '';

    static $_instance = null;

    public function __construct( $secret ){
        $this->_secret = $secret;
        $this->_payload['iat'] = time();
    }

    /**
     * @param $secret 签名秘钥
     * @return JwtToken
     */
    public static function getInstance( $secret ){
        if( !self::$_instance ){
            self::$_instance = new self( $secret );

        }
        return self::$_instance;
    }

    /**
     * 生成JWT签名串
     * @return string
     */
    public function gen(){
        $header_payload = $this->encode( $this->getHeader() ).'.'.$this->encode($this->getPayload());
        return $header_payload .'.'.$this->getSignature( $header_payload );
    }

    /**
     * 获取头部
     * @return array|mixed
     */
    public function getHeader(){
        if(isset($this->_jwt[0])){
            return $this->_jwt[0];
        }
        return [
            'alg'=>$this->_algorithm,
            'typ'=>"JWT"
        ];
    }

    /**
     * 获取内容
     * @return array|mixed
     */
    public function getPayload(){
        if(isset($this->_jwt[1])){
            return $this->_jwt[1];
        }
        return $this->_payload;
    }

    /**
     * 设置自定义的内容体
     * @param array $data
     * @return $this
     */
    public function setCustom(array $data ){
        $this->_payload = array_merge($data,$this->_payload);
        return $this;
    }

    /**
     * 设置创建者
     * @param $issuer
     * @return $this
     */
    public function setIssuer($issuer){
        $this->_payload['iss'] = $issuer;
        return $this;
    }

    /**
     * 设置主题
     * @param $subject
     * @return $this
     */
    public function setSubject($subject){
        $this->_payload['sub'] = $subject;
        return $this;
    }

    /**
     * 设置受众
     * @param $audience
     * @return $this
     */
    public function setAudience($audience){
        $this->_payload['aud'] = $audience;
        return $this;
    }

    /**
     * 设置过期时间
     * @param $expire_at
     * @return $this
     */
    public function setExpireAt($expire_at){
        $this->_payload['exp'] = $expire_at;
        return $this;
    }

    /**
     * 设置有效时长
     * @param $expire
     */
    public function setExpire( $expire ){
        $this->_payload['exp'] = ( isset( $this->_payload['iat'] ) ? $this->_payload['iat'] : time() ) + $expire;
        return $this;
    }

    /**
     * 设置开始生效时间
     * @param $not_before
     * @return $this
     */
    public function setNotBefore($not_before){
        $this->_payload['nbf'] = $not_before;
        return $this;
    }

    /**
     * 设置创建时间
     * @param $issued_at
     * @return $this
     */
    public function setIssuedAt($issued_at){
        $this->_payload['iat'] = $issued_at;
        return $this;
    }

    /**
     * 设置jwt ID
     * @param $jwt_id
     * @return $this
     */
    public function setJwtId($jwt_id){
        $this->_payload['jti'] = $jwt_id;
        return $this;
    }

    /**
     * 设置签名秘钥
     * @param $secret
     * @return $this
     */
    public function setSecret($secret){
        $this->_secret = $secret;
        return $this;
    }

    /**
     * 设置签名算法（消息哈希算法）
     * @param $algorithm
     * @return $this
     */
    public function setAlgorithm( $algorithm ){
        $algorithms = $this->getAlgorithms();
        if( in_array( strtolower($algorithm),$algorithms ) ){
            $this->_algorithm = strtoupper( $algorithm );
        }else{
            trigger_error('Not Support Algorithm, use getAlgorithms method to get supported',E_USER_NOTICE);
        }
        return $this;
    }

    /**
     * 重置内容体
     * @return $this
     */
    public function reset(){
        $this->_payload =[ 'iat'=> time() ];
        return $this;
    }

    /**
     * 是否加密内容 请开启openssl拓展
     * @param $encrypted
     * @return $this
     */
    public function setEncrypted( $encrypted ){
        $this->_encrypted = $encrypted;
        return $this;
    }

    /**
     * 验证JWT字符串是否有效
     * @param $jws
     * @return bool
     */
    public function verify( $jws ){
        $this->_jwt = explode('.',$jws);
        if( count( $this->_jwt ) !=3 ){
            return false;
        }
        $_header_org = $this->_jwt[0];
        $_header = $this->decode( $_header_org );
        if( empty( $_header ) ){
            return false;
        }
        $_payload_org = $this->_jwt[1];
        $_payload = $this->decode( $_payload_org );
        if( empty($_payload) ){
            return false;
        }
        $this->_jwt[1] = json_decode($_payload,true);
        if(isset($this->_jwt[1]['exp']) && time() > $this->_jwt[1]['exp'] ){
            return false;
        }
        $this->_jwt[0] = json_decode($_header,true);
        if( empty($this->_jwt[0]) || !$this->checkAlgorithms( $this->_jwt[0]['alg'] ) ){
            return false;
        }
        $this->_algorithm = $this->_jwt[0]['alg'];
        if( $this->_jwt[2] != $this->getSignature($_header_org.'.'.$_payload_org) ){
            return false;
        }
        return true;

    }

    /**
     * 获得支持的签名算法
     * @return array
     */
    public function getAlgorithms(){
        return hash_algos();
    }

    /**
     * 判断是否合法的签名算法
     * @param $algorithm
     * @return bool
     */
    public function checkAlgorithms( $algorithm ){
        return in_array( strtolower($algorithm), $this->getAlgorithms() );
    }

    private function encode( $string ){
        if( $this->_encrypted ){
            if( !is_string($string) ){
                $string = json_encode($string);
            }
            $string = openssl_encrypt($string,'AES256',$this->_secret);
        }
        return self::base64UrlEncode( $string );
    }

    private function decode( $string ){
        $_string = self::base64UrlDecode( $string );
        if( $this->_encrypted ){
            $_string = openssl_decrypt($_string,'AES256',$this->_secret);
        }
        return $_string;
    }

    /**
     * base64改造加密算法
     * @param $string
     * @return mixed
     */
    public static function base64UrlEncode( $string ){
        if( !is_string($string) ){
            $string = json_encode($string);
        }
        return str_replace(['+','/','='],['-','_',''], base64_encode($string) );
    }

    /**
     * base64改造解密算法
     * @param $string
     * @return bool|string
     */
    public static function base64UrlDecode( $string ){
        return base64_decode( str_replace(['-','_'],['+','/'], $string) );
    }

    /**
     * 获取签名
     * @param $header_payload
     * @return string
     */
    private function getSignature( $header_payload ){
        return hash_hmac( strtolower($this->_algorithm), $header_payload, $this->_secret );
    }

}
