<? 
class Url {
	
	const METHOD_GET  = 1;
	const METHOD_POST = 2;

	
	public static function get($url) {
		return self::client($url, self::METHOD_GET);
	}
	
	public static function post($url, $data) {
		return self::client($url, self::METHOD_POST, $data);
	}
	
	
	private static function client($url, $method=self::METHOD_GET, $data=NULL) {
		
    if (($method == self::METHOD_POST) && empty($data)) {
    	trigger_error(__CLASS__ . ":: Nil POST data supplied.", E_USER_ERROR);
    	return NULL;
    }

    $requestHeaders = array();
    //$requestHeaders[] = "Content-type: 	application/x-www-form-urlencoded; charset=UTF-8";
    //$requestHeaders[] = "Content-Length: 23";
    //$requestHeaders[] = "SOAPAction: http://services.xpl.com.au/Host/Provider/IProviderService/" . $action;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 80);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    
    switch($method) {
    	case self::METHOD_POST:
    		curl_setopt($ch, CURLOPT_POST, TRUE);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    		break;
    	default:
    		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
    		break;
    }
		
    //curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/mm');
	 //curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/mm');
	
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:13.0) Gecko/20100101 Firefox/13.0.1'); 
    if(count($requestHeaders) > 0) {
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
    }
        
    if(FALSE === ($response = curl_exec($ch))) {
    	$errNo = curl_errno($ch);
    	$errMsg = curl_error($ch);
    	trigger_error(__CLASS__ . "::cUrl Error (" . $errNo. ") " .$errMsg, E_USER_ERROR);
    	$response = NULL;
    }
	  	 
    return $response;
  }	
}
?>