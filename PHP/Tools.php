<?php
class Tools {
	/**
	 * @param $params
	 * @title 获取签名参数,参数列表 secret:xxxxx param_key1:param_val1 param_key1:param_val1
	*/
	public function getSign ( $params ){
		$_params = [];
		foreach( $params as $item ){
			list( $key,$value ) = explode(':',$item,2);
			if( empty($key) ){
				continue;
			}
			$_params[$key] = $value;
		}
		if( empty($_params['secret']) ){
			exit('secret is necessary');
		}
		$secret = $_params['secret'];
		unset($_params['secret']);
		return $this->generateAccessToken($secret,$_params);
	}

	/**
	 * @param $params
	 * @title 获取签名参数(新方式),参数列表 secret:xxxxx param_key1:param_val1 param_key1:param_val1
	*/
	public function getNewSign ( $params ){
		$_params = [];
		foreach( $params as $item ){
			list( $key,$value ) = explode(':',$item,2);
			if( empty($key) ){
				continue;
			}
			$_params[$key] = $value;
		}
		if( empty($_params['secret']) ){
			exit('secret is necessary');
		}
		$secret = $_params['secret'];
		unset($_params['secret']);
		return $this->generateAccessToken($secret,$_params,true);
	}
	/**
	 * @param $params
	 * @title 表达式测试,参数为表达式子串
	*/
	public function test( $params ){
		if( empty($params[0]) ){
			exit('Need a Expression');
		}
		$_user_info = array(
            'language' => 'zh',
            'displayWidth' => 400,
            'displayHeight' => 800,
            'appVersion' => '1.1.2',
            'os' => 'win',
            'osVersionCode' => 7,
            'deviceType' => 'pc',
            'sdkVersion' => 'v1.2.3'
        );
        echo $args = implode(',$',array_keys($_user_info));
		$functions = @create_function('$'.$args,'return '.$params[0].';');
		$result = $functions(...array_values($_user_info));
		var_dump(!empty($result));
	}

	/**
	 * @param $params
	 * @title 获取签名参数(新方式),参数列表 times:50 version1:weight1 version2:weight2 version3:weight3 ...
	*/
	public function getVersion ( $params ){
		$_params = [];
		foreach( $params as $item ){
			list( $key,$value ) = explode(':',$item,2);
			if( empty($key) ){
				continue;
			}
			$_params[$key] = $value;
		}
		if( empty($_params) ){
			exit('param is necessary');
		}
		$times = 1;
		if(!empty($_params['times'])){
			$times = intval($_params['times']);
			unset($_params['times']);
		}
		$versions = [];
		for($i=$times;$i>0;$i--){
			$version = self::getRandom($_params);
			$versions[$version] += 1;
		}
		return $versions;
	}

	/**********Private Methods***********/
	private static function generateAccessToken($secret,$param,$is_new=false){
    	$token = $is_new ? '' : $secret;
    	$token .= self::loopArrayToken($param);
    	$token .= $secret;
    	$token = strtoupper(md5($token));
    	return $token;
    }
    
    private static function loopArrayToken($param) {
    	$token = "";
    	ksort($param);
    	foreach ($param as $k => $v) {
    		if (is_array($v)) {
    			$token .="{$k}";
    			$token .= self::loopArrayToken($v);
    		} else {
    			$token .= "{$k}{$v}";
    		}
    	}
    	return stripslashes($token);
    }

    private static function getRandom($_splits = array(50,50),$random_chance=NULL){
        $_random_chance = is_numeric($random_chance) ? (int) $random_chance : mt_rand(0, array_sum($_splits)-1);
        $chosen_version = false;
        $start_point = 0;
        $i=1;
        $default = NULL;
        foreach ($_splits as $version => $probability) {
            if( $i-->0 ){
                $default = $version;
            } 
            if ($_random_chance >= $start_point && $_random_chance < ($start_point + $probability)) {
                $chosen_version = $version;
                break;
            }
            $start_point += $probability;
        }
        // Security fallback
        if (false === $chosen_version) {
            $chosen_version = $default;
        }
        return $chosen_version;
    }
	
}
$argv = array_slice($argv,1);
$tool = new Tools();
$method = empty($argv) ? null : $argv[0];
$param = array_slice($argv,1);
if( empty($method) ){
	$methods = ( new ReflectionClass('Tools') )->getMethods( ReflectionMethod::IS_PUBLIC );
	echo 'Method Lists:',PHP_EOL;
	foreach( $methods as $method_reflect ){
		$item = $method_reflect->getDocComment();
		preg_match('/@title\s+(.*)/i',$item,$match);
		echo "\t",str_pad($method_reflect->name,16," "),iconv('utf-8','gbk',empty($match[1])?'':$match[1]),PHP_EOL;
	}
	exit();
}
print_r( $tool->$method($param) );

?>
