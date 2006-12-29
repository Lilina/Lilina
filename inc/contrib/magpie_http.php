<?php

/**
    MagpieHTTP, an HTTP client thats owes a deep debt of gratitude to Snoopy and HTTP::Request
        
    
    @copyright Kellan Elliott-McCrea <kellan@pobox.com>
    @license   dual licensed GPL/BSD
        
*/
class Magpie_HTTP {
    
    // request
    var $_url;       // url to request
    var $_host;
    var $_port = 80;
    var $_path;
    var $_scheme;
    var $_requestHeaders;
    
    var $_auth_type     = 'Basic';  // default auth type
    var $_method        = "GET";
    var $_httpversion   = "HTTP/1.0";
    var $_accept_gzip   = true;     // use gzip encoding if true AND zlib extension available
    
    // proxy
    var $_proxy_host;
    var $_proxy_port;
    var $_proxy_user;
    var $_proxy_pass;
    var $_is_proxy = false;
    
    
    // response variables
	var $body        = '';
	var $error;
	var	$headers	    = array();			// headers returned from server sent here
	var $cookies        = array();
	
	var $is_gzipped     = false;
	var $timed_out		= false;				// if a read operation timed out
	
	var	$status			=	0;					// http request status
	
	
	// redirects
	var $redirected         = false;
	var $_redirects         = 0;
	var $_max_redirects     = 5;
	var $_allow_redirects 	= true;
	#var $_new_permanent_url = '';              // update this with any 301s we see, see isPermanentRedirect()
										
	// internals
    var	$_maxlength		=	500000;				// max return data length (body)
	var	$_maxlinelen	=	4096;				// max line length (headers)
	
	var $_connect_timeout =	15;					// timeout for socket connection
	var $_read_timeout	 =	15;					// timeout on read operations, in seconds
												// set to 0 to disallow timeouts
    var $_accept			=	"*/*";
	
    
    /**
        GET $url
    */
    function fetch($url) {
        $this->_reset_response();
        
        if (!$this->_setUrl($url)) {
            return false;
        }
        
        if ($this->_connect($fp)) {
            $request = $this->buildRequest();
            
            if (fwrite($fp,$request,strlen($request))) {
                if ($this->processResponse($fp)) {
                    $this->_disconnect($fp);
                    
                    // redirect?
                    if ($this->isRedirect()) {
                        if ($this->isUnchanged()) {
                            return true;
                        }
                        else {
                            $this->handleRedirect();
                        }
                    }
                    elseif ($this->isSuccess()) {
                        return true;
                    }
                    else {
                        return false;
                    }    
                } // end if ($this->processResponse($fp))
            }
            else {
                $this->error = "socket error, write failed";
            }
        } // end if ($this->_connect($fp)) 
        
        return false;
    } // end fetch
    
    function handleRedirect() {
        // 304 isn't really a redirect
        if ($this->isUnchanged()) {
            return;
        }
        
        if (!empty($this->headers['location'])) {
            if ($this->_allow_redirects and $this->_redirects < $this->_max_redirects) {
                if(!preg_match("|\:\/\/|", $this->headers['location'])) {
                    $this->_redirectaddr = $this->_scheme . '://' . $this->_host . ":" . $this->_port;
            
                    // eliminate double slash
                    if(!preg_match("|^/|", $this->headers['location'])) {
							$this->_redirectaddr .= "/". $this->headers['location'];
					}
					else {
							$this->_redirectaddr .=  $this->headers['location'];
					}
                }
                else {
                    $this->_redirectaddr = $this->headers['location'];
                }
                $this->_redirects++;
                $this->redirected = true;
                
                
                // XXX: this should really maintain redirect stack
                if ($this->status == 301) {
                    $this->_new_permanent_url = $this->_redirectaddr;
                }
             
                $this->fetch($this->_redirectaddr);
            }
        }
    }
    
    function addHeader($name, $value) {
        $this->_requestHeaders[strtolower($name)] = $value;
    }
    
    function addDefaultHeader($name, $value) {
        if(!isset($this->_requestHeaders[strtolower($name)])) {
            $this->addHeader($name, $value);
        }
    }
    
    
    
    function setProxy($host, $port = 8080, $user = null, $pass = null) {
        $this->_proxy_host = $host;
        $this->_proxy_port = $port;
        $this->_proxy_user = $user;
        $this->_proxy_pass = $pass;
        $this->_is_proxy = true;
        
        if (!empty($user)) {
            $this->addHeader('Proxy-Authorization', 'Basic ' . base64_encode($user . ':' . $pass));
        }
    }
    
    function setAgent($agent) {
        $this->_agent = $agent;
        $this->addHeader('User-Agent', $this->_agent);
    }

    function setLogin($user, $pass, $auth_type = 'Basic') {
        $this->_user = $user;
        $this->_pass = $pass;
        $this->_auth_type = $auth_type;
    }

    function setEtag($etag) {
        $this->addHeader('If-None-Match', $etag);
    }

    function setLastModified($last_modified) {
        $this->addHeader('If-Modified-Since', $last_modified);
    }
    
    
    function buildRequest() {
        if ($this->_is_proxy) {
            $url = $this->_url;
        }
        else {
            $url = $this->_path;
        }
        
        $request = $this->_method . ' ' . $url . ' ' . $this->_httpversion . "\r\n";
        
        $this->addDefaultHeader('Connection', 'close');
        
         // Basic authentication
        if (!empty($this->_user) and $this->_auth_type == 'Basic') {
            $this->addDefaultHeader('Authorization', 'Basic ' . base64_encode($this->_user . ':' . $this->_pass));    
        }
            
		if(!empty($this->_host)) {
            $this->addDefaultHeader('Host', $this->_host);
		}
	    
	    if(!empty($this->_accept)) {
			$this->addDefaultHeader('Accept', $this->_accept);
        }
        
        if ($this->_accept_gzip and function_exists('gzinflate')) {
            $this->addHeader('Accept-Encoding', 'gzip');
        }
        
        // Request Headers
        if (!empty($this->_requestHeaders)) {
            foreach ($this->_requestHeaders as $name => $value) {
                $canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
                $request      .= $canonicalName . ': ' . $value . "\r\n";
            }
        }

        $request .= "\r\n";

    	return $request;   
    } 
    
    function processResponse($fp) {
        $line = $this->_sock_readline($fp);
        
        if (sscanf($line, 'HTTP/%s %s', $http_version, $returncode) != 2) {
            $this->error = "Mangled response.";
            return false;
        }
        $this->status = $returncode;
        
        while ($line = $this->_sock_readline($fp)) {
            // terminate on blank line, even a malformed one
			if(preg_match("/^\r?\n$/", $line) ) {
    		    break;
            }
            $this->processHeader($line);
        }
        
        $this->is_gzipped = isset($this->headers['content-encoding']) && ('gzip' == $this->headers['content-encoding']);
        $hasBody = false;
        $body = '';
        if (!isset($this->headers['content-length']) || 0 != $this->headers['content-length']) {
            while ($data = fread($fp, $this->_maxlength)) {
                $hasBody = true;
                $body .= $data;
                if (strlen($body) > $this->_maxlength) {
                    break;
                }
            }
        }
        
        if ($hasBody and $this->is_gzipped) {
            $body = gzinflate(substr($body, 10));
        }
        
        $this->body = $body;
        
        if ($this->_read_timeout > 0 && $this->_check_timeout($fp)) {
    	    $this->status=-100;
    		return false;
    	}

        
    	return true;
    }
    
    function processHeader($header) {
        list($headername, $headervalue) = explode(':', $header, 2);
        $headername  = strtolower($headername);
        $headervalue = ltrim($headervalue);

        if ('set-cookie' != $headername) {
            if (isset($this->headers[$headername])) {
                $this->headers[$headername] .= ',' . $headervalue;
            }
            else {
                $this->headers[$headername]  = trim($headervalue);
            }
        }
        else {
            $this->parseCookie($headervalue);
        }
    }
    
    function parseCookie($headervalue) {
        $cookie = array(
            'expires' => null,
            'domain'  => null,
            'path'    => null,
            'secure'  => false
        );

        // Only a name=value pair
        if (!strpos($headervalue, ';')) {
            $pos = strpos($headervalue, '=');
            $cookie['name']  = trim(substr($headervalue, 0, $pos));
            $cookie['value'] = trim(substr($headervalue, $pos + 1));

        // Some optional parameters are supplied
        } else {
            $elements = explode(';', $headervalue);
            $pos = strpos($elements[0], '=');
            $cookie['name']  = trim(substr($elements[0], 0, $pos));
            $cookie['value'] = trim(substr($elements[0], $pos + 1));

            for ($i = 1; $i < count($elements); $i++) {
                if (false === strpos($elements[$i], '=')) {
                    $elName  = trim($elements[$i]);
                    $elValue = null;
                } else {
                    list ($elName, $elValue) = array_map('trim', explode('=', $elements[$i]));
                }
                $elName = strtolower($elName);
                if ('secure' == $elName) {
                    $cookie['secure'] = true;
                } elseif ('expires' == $elName) {
                    $cookie['expires'] = str_replace('"', '', $elValue);
                } elseif ('path' == $elName || 'domain' == $elName) {
                    $cookie[$elName] = urldecode($elValue);
                } else {
                    $cookie[$elName] = $elValue;
                }
            }
        }
        $this->cookies[] = $cookie;
    }
    
    function isInfo () { 
        return $this->status >= 100 && $this->status < 200; 
    }

    function isSuccess () { 
        return $this->status >= 200 && $this->status < 300; 
    }

    function isRedirect () { 
        return $this->status >= 300 && $this->status < 400; 
    }

    /**
        ATTENTION AGGREGATOR DEVELOPERS:  on permanent redirect you MUST update
        your database with the $http->finalUrl
    */
    function isPermanentRedirect() {
        return $this->status == 301;
    }
    
    function isUnchanged() {
        return $this->status == 304;
    }
    
    /**
        ATTENTION AGGREGATOR DEVELOPERS: the feed is GONE.  It isn't coming back, unsubscribe.
    */
    function isGone() {
        return $this->status == 410;
    }
    
    function isError () { 
        return $this->status >= 400 && $this->status < 600; 
    }

    function isClientError () { 
        return $this->status >= 400 && $this->status < 500; 
    }
    
    function is404() {
        return $this->status == 404;
    }

    function isServerError () { 
        return $this->status >= 500 && $this->status < 600; 
    }
    
    function isSocketError() {
        return $this->status < 1;
    }
    
    function isTimeout() {
        return $this->status == -100;
    }
    
    function _setUrl($url) {
        $this->_url = $url;
        $uri_parts = parse_url($url);
        
        if (empty($uri_parts['host'])) {
            $this->error = 'bad url.';
            return false;
        }
        
        if (!empty($uri_parts['user'])) {
            $this->_user = $uri_parts['user'];
        }
        
        if (!empty($uri_parts['pass'])) {
            $this->_pass = $uri_parts['pass'];
        }
        
        $this->_host = $uri_parts['host'];
		
		if(!empty($uri_parts['port'])) {
			$this->_port = $uri_parts['port'];
		}
		
		if (isset($uri_parts['path'])) {
		    $this->_path = $uri_parts['path'];
        }
        else {
            $this->_path = "/";
        }
        
		if (isset($uri_parts['query'])) {
		    $this->_path .= '?'.$uri_parts['query'];
		}
		
		$this->_scheme = $uri_parts['scheme'];	
		
		return true;
    }
    
    function _connect(&$fp) {
        if(!empty($this->_proxy_host) && !empty($this->_proxy_port)) {
            $host = $this->_proxy_host;
            $port = $this->_proxy_port;
        }
        else {
            $host = $this->_host;
            $port = $this->_port;
        }

        $this->status = 0;
        
        if($fp = fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            $this->_connect_timeout )) 
        {
            // socket connection succeeded
            // set the read timeout if needed
            if ($this->_read_timeout > 0) {
                socket_set_timeout($fp, $this->_read_timeout);
            }

            return true;
        }
        else {
            // socket connection failed
            $this->status = $errno;
            switch($errno) 
            {
                case -3:
                    $this->error="socket creation failed. $errstr  (-3)";
                case -4:
                    $this->error="dns lookup failure. $errstr  (-4)";
                case -5:
                    $this->error="connection refused or timed out. $errstr (-5)";
                default:
                    $this->error="connection failed. $errstr (".$errno.")";
            }
            return false;
        }
    }    
    
    function _disconnect($fp) {
		return(fclose($fp));
	}
	
    function _sock_readline($fp) {
        $line = fgets($fp, $this->_maxlinelen);
        return $line;
    }
    
    function _check_timeout($fp) {
		if ($this->_read_timeout > 0) {
			$fp_status = socket_get_status($fp);
			if ($fp_status["timed_out"]) {
				$this->timed_out = true;
				return true;
			}
		}
		return false;
	}
	
	function timeout() {
	    return $this->_read_timeout + $this->_connect_timeout;
	}
	
	function _reset_response() {
        $this->body = '';
        $this->error = '';
        $this->headers = array();
        $this->cookies = array();
        $this->is_gzipped = false;
        $this->timed_out = false;
        $this->status = 0;
        
    }
    
    
    
} // end MagpieHTTP

?>