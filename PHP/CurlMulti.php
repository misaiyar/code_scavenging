<?php

class CurlMulti{

  public function multi($urls){
	  
	  $mh = curl_multi_init();
	  $chs = [];
		foreach($urls as $url){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // don't check certificate
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // don't check certificate
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_multi_add_handle($mh,$ch);
			$chs [] = $ch;
		}
		$active = null;
		do {
		    $mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
		    if (curl_multi_select($mh) != -1) {
		        do {
		            $mrc = curl_multi_exec($mh, $active);
		        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
		    }
		}
    $return = [];
		foreach($chs as $ch){
			$return[] = $content = curl_multi_getcontent($ch);
			//echo $content;
			curl_multi_remove_handle($mh, $ch);// 关闭全部句柄
		}
		curl_multi_close($mh);
	}
  
  function yieldcurl( $urls ){
		foreach($urls as $url){
			$ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, $url);//改成https后，灰度环境无法访问该接口
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // don't check certificate
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // don't check certificate
	        yield curl_exec($ch);
	    }
	}
}
