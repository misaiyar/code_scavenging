<?php

class LdapClient
{
    private $di = null;
    private $redis = null;

    public function __construct( $di ){
        $this->di = $di;
        $this->redis = $di->get('redis');
    }

    public static function checkLDAP($username, $password){
        $config = Common::importConfigFile(CONF_PATH . "/config.php"); //加载配置
        $connect = ldap_connect($config['ldap']['host'],$config['ldap']['port']);
        if($connect){
            //匿名绑定
            $res = @ldap_bind($connect);
            if($res){
                $filter = "(|(uid={$username})(mail={$username}))";
                //过滤
                $search = ldap_search($connect, $config['ldap']['dn'], $filter);
                if($search){
                    $info = ldap_get_entries($connect, $search);
                    if(isset($info[0]['dn'])){
                        $dn = $info[0]['dn'];
                        //密码错误，则返回匿名时信息，没有userpassword
                        $ressult = @ldap_bind($connect, $dn, $password);
                        if(true === $ressult){
                            ldap_close($connect);
                            return array(
                                'email' => $info[0]['mail'][0],
                                'username' => $info[0]['uid'][0],
                                'realname' => $info[0]['cn'][0],
                            );
                        }
                    }
                }
            }
            ldap_close($connect);
        }
        return false;
    }
}
