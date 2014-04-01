<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class custom_functions {
	// fonction pour extraire un string au millieu de deux autres
	
	public function GetBetween($content,$start,$end){
    $r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return 'walou';
	}
	
	public function delete_between($string, $beginning, $end) {
	  $beginningPos = strpos($string, $beginning);
	  $endPos = strpos($string, $end);
	  if (!$beginningPos || !$endPos) {
		return $string;
	  }
	
	  $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

	  return str_replace($textToDelete, '', $string);
	}
	
	// fonction pour identifier et extraire tous les strings qui contiennent chi le3ba
	public function matchi_koulchi($regex, $str, $i = 0){
        if(preg_match_all($regex, $str, $matches) === false)
            return false;
        else
            return $matches[$i];
    }
 
 	// fonction pour identifier et extraire 1 string qui contien chi le3ba
    public function matchi($regex, $str, $i = 0){
        if(preg_match($regex, $str, $match) == 1)
            return $match[$i];
        else
            return false;
    }
	
	// Fonction Multi Curl 
	
	public function multi_curl($scraping_requests){
		
		$global_exec = curl_multi_init(); 
		
		for ($i = 0; $i < count($scraping_requests); $i++){
		
		$c_ex[$i] = curl_init(); 
		curl_setopt($c_ex[$i], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c_ex[$i], CURLOPT_URL, $scraping_requests[$i]);  
		curl_setopt($c_ex[$i], CURLOPT_HEADER, 0);
		curl_multi_add_handle($global_exec,$c_ex[$i]);  
			
		}
		
		$active_requests = null;
		
		// Exécute le gestionnaire
		do {
    		$parall_exec = curl_multi_exec($global_exec, $active_requests);
			} while ($parall_exec == CURLM_CALL_MULTI_PERFORM);

				while ($active_requests && $parall_exec == CURLM_OK) {
    				if (curl_multi_select($global_exec) != -1) {
        				do {
            		$parall_exec = curl_multi_exec($global_exec, $active_requests);
        		} while ($parall_exec == CURLM_CALL_MULTI_PERFORM);
    			}
			}
		
		for ($i = 0; $i < count($scraping_requests); $i++){
		
		$scrap_statu[$i] = curl_multi_getcontent($c_ex[$i]);
		
		curl_multi_remove_handle($global_exec,$c_ex[$i]);
			
		}
		
		return $scrap_statu;
		
		curl_multi_close($global_exec);  
		
	}
	
	public function multi_curl_extern($scraping_requests){
		
		$global_exec = curl_multi_init(); 
		
		for ($i = 0; $i < count($scraping_requests); $i++){
		
			$c_ex[$i] = curl_init(); 
			curl_setopt($c_ex[$i], CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c_ex[$i], CURLOPT_URL, $scraping_requests[$i]);  
			
			$ip=rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/".rand(3,5).".".rand(0,3)." (Windows NT ".rand(3,5).".".rand(0,2)."; rv:2.0.1) Gecko/20100101 Firefox/".rand(3,5).".0.1");		
			
			curl_multi_add_handle($global_exec,$c_ex[$i]);  
			
		}
		
		$active_requests = null;
		
		// Exécute le gestionnaire
		do {
    		$parall_exec = curl_multi_exec($global_exec, $active_requests);
			} while ($parall_exec == CURLM_CALL_MULTI_PERFORM);

				while ($active_requests && $parall_exec == CURLM_OK) {
    				if (curl_multi_select($global_exec) != -1) {
        				do {
            		$parall_exec = curl_multi_exec($global_exec, $active_requests);
        		} while ($parall_exec == CURLM_CALL_MULTI_PERFORM);
    			}
			}
		
		for ($i = 0; $i < count($scraping_requests); $i++){
		
		$scrap_statu[$i] = curl_multi_getcontent($c_ex[$i]);
		
		curl_multi_remove_handle($global_exec,$c_ex[$i]);
			
		}
		
		return $scrap_statu;
		
		curl_multi_close($global_exec);  
		
	}
	
	public function link_normaliser ($data){
		
		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
		
		$normalised = str_replace($search, $replace, $data);
		
		return str_replace('-', ' ', $normalised);
	
	}
	
	public function url_norm($data){
		
		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
		
		$normalised = str_replace($search, $replace, $data);
		
		return $normalised;
	
	}
		
	public function in_multi_array($needle, $haystack)
    {
        foreach ($haystack as $item) {
        if ($item === $needle || (is_array($item) && in_multi_array($needle, $item))) {
            return true;
        }
    }
 
    return false;
	
	}
	
	public function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}
	
	public function Get_url($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $ip=rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/".rand(3,5).".".rand(0,3)." (Windows NT ".rand(3,5).".".rand(0,2)."; rv:2.0.1) Gecko/20100101 Firefox/".rand(3,5).".0.1");
        $json = curl_exec($ch);
        curl_close($ch);
        return $json;
    }
	
	public function curl_post($url,$post){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($ch);
		curl_close ($ch);	
        return $json;
    }
	
	public function Get_url_cookie($url,$cookie){
		$COOKIE_FOLDER= __DIR__;
		
        $ch = curl_init();
		
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		
        $ip=rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
		
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/".rand(3,5).".".rand(0,3)." (Windows NT ".rand(3,5).".".rand(0,2)."; rv:2.0.1) Gecko/20100101 Firefox/".rand(3,5).".0.1");
		
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $COOKIE_FOLDER.'_spr_cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FOLDER.'_spr_cookie.txt');
		
        $json = curl_exec($ch);
		
        curl_close($ch);
		
        return $json;
    }
	
	public function jsonp_decode($jsonp, $assoc = true) { // PHP 5.3 adds depth as third parameter to json_decode
    if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
       $jsonp = substr($jsonp, strpos($jsonp, '('));
    }
    return json_decode(trim($jsonp,'();'), $assoc);
	}

			
	
}

?>