<?php


class Magpie_Cache {
    var $base_cache  = './cache';    // where the cache files are stored
    var $max_age    = 3600;         // when are files stale, default one hour
    
    var $_cache_error      = "";           // accumulate error messages
    
    
    
    function Magpie_Cache ($base='', $age='') {
        if ( $base ) {
            $this->base_cache = $base;
        }
        if ( $age ) {
            $this->max_age = $age;
        }
        
        // attempt to make the cache directory
        if ( ! file_exists( $this->base_cache ) ) {
            $status = @mkdir( $this->base_cache, 0755 );
            
            // if make failed 
            if ( ! $status ) {
                $this->error(
                    "Cache couldn't make dir '" . $this->base_cache . "'."
                );
                return false;
            }
        }
        
        if (is_writable($this->base_cache)) {
            return true;
        }
        else {
            $this->error('Cache unwritable: ' . $this->base_cache);
            return false;
        }
    }
    
    /**
      cache feed
     
    */
    function set ($key, $feed) {
        
        $cache_file = $this->file_name( $key );
        $fp = @fopen( $cache_file, 'w' );
        
        if ( ! $fp ) {
            $this->error(
                "Cache unable to open file for writing: $cache_file"
            );
            return 0;
        }
        
        
        $data = $this->serialize( $feed );
        fwrite( $fp, $data );
        fclose( $fp );
        
        return $cache_file;
    }
    
    /**
      fetch serialized feed obj from cache
     
      return feed obj on HIT, false on MISS
    */
    function get ($key, $max_age=false) {
        $cache_file = $this->file_name( $key );
        
        if ( ! file_exists( $cache_file ) ) {
            $this->debug( 
                "Cache doesn't contain: $key (cache file: $cache_file)"
            );
            return 0;
        }
        
        $fp = @fopen($cache_file, 'r');
        if ( ! $fp ) {
            $this->error(
                "Failed to open cache file for reading: $cache_file"
            );
            return 0;
        }
        
        if ($filesize = filesize($cache_file) ) {
        	$data = fread( $fp, filesize($cache_file) );
        	$feed = $this->unserialize( $data );
        
            $age = $this->cache_age($key);
            $feed->from_cache = 1 + $age;  // make sure age is greater then 1 so it evaluates to true
            $feed->cache_status = $this->cache_status($age, $max_age);
        	
        	return $feed;
    	}
    	
    	return 0;
    }

    /**
      feed exists in cache?
     
      return cache status 'HIT', 'MISS', or 'STALE'
    */
    function check_cache ( $key, $max_age=false ) {
        $age = $this->cache_age($key);
        return $this->cache_status($age, $max_age);
    }
    
    function cache_status($age, $max_age=false) {
        $max_age = $max_age ? $max_age : $this->max_age;
        
        if (is_null($age)) {
            return 'MISS';
        }
        elseif ($max_age > $age) {
            return 'HIT';
        }
        else {
            return 'STALE';
        }
    }

	function cache_age( $key ) {
		$filename = $this->file_name( $key );
		if ( file_exists( $filename ) ) {
			$mtime = filemtime( $filename );
            $age = time() - $mtime;
			return $age;
		}
		else {
			return null;	
		}
	}
	
    function serialize ( $rss ) {
        return serialize( $rss );
    }

    function unserialize ( $data ) {
        return unserialize( $data );
    }
    
    /**
       map cache key to filename
    */
    function file_name ($url) {
        $filename = md5( $url );
        return join( DIRECTORY_SEPARATOR, array( $this->base_cache, $filename ) );
    }

    function ok() {
        return !$this->_cache_error;
    }
    
    function error ($errormsg=false) {
        if ($errormsg) {
            if ( isset($php_errormsg) ) { 
                $errormsg .= " ($php_errormsg)";
            }
            $this->_cache_error = $errormsg;
        }
        return $this->_cache_error;
    }
    
    function debug ($debugmsg, $lvl=E_USER_NOTICE) {
        // if ( MAGPIE_DEBUG ) {
        //     $this->error("MagpieRSS [debug] $debugmsg", $lvl);
        // }
    }
}

?>