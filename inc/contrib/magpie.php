<?php

/**
    Magpie, a simple RSS/Atom parser, and feed integration tool for PHP.
    
    @copyright Kellan Elliott-McCrea <kellan@pobox.com>
    @license   dual licensed GPL/BSD
        
*/


function fetch_rss($url, $options=array()) {
    $magpie = new Magpie();
    $feed = $magpie->fetch($url, $options);
    // set global pointer for helper methods
    $GLOBALS['__MAGPIE_LAST_INSTANCE'] = $magpie; 
    return $feed;
}

function magpie_error() {
    return $GLOBALS['__MAGPIE_LAST_INSTANCE']->error();
}

class Magpie {
    var $VERSION        = 'alpha';
    
    var $HTTP_CLASS     = 'Magpie_HTTP';
    var $PARSER_CLASS   = 'Magpie_Parser';
    var $CACHE_CLASS    = 'Magpie_Cache';
    
    var $http;
    var $parser;
    var $cache;
    var $source;
    
    var $trigger_errors = true;
    var $_magpie_error  = '';
    
    function Magpie() {
        $this->init_defaults();
    }
    
    function fetch($url, $options=array()) {
        $options = $this->options($options);
        
        $feed = false;
        
        if(!isset($url)) {
            $this->error("Blank feed URL");
            return false;
        }
        
        // load cache, on by default, die on error
        $cache_status   = null;
        $cached_feed    = null;
        
        if ($options['cache_on']) {
            $cache = $this->cache($options);
            if(!$cache->ok()) {
                $this->error('Cache error: ' . $cache->error());
                return false;
            }
        
            // check cache for fresh feed
        
            $cache_key      = $url . $options['output_encoding'];
            $cache_status   = $cache->check_cache($cache_key);
        
            if ( $cache_status === 'HIT' ) {
                $feed = $cache->get( $cache_key );
                return $feed;
            }
        }
        // else cache is off
        else {
            $cache          = null;
            $options['http_user_agent'] = $options['http_user_agent_no_cache'];
        } 
        
        $http = $this->http($options);
        
        // use cached headers to setup conditional get
        
        if ( $cache_status === 'STALE' ) {
            $cached_feed = $cache->get( $cache_key );
            if ( $cached_feed ) {
                if ($cached_feed->etag()) {
                    $http->setEtag($cached_feed->etag());
                }
                    
                if ($cached_feed->last_modified()) {
                    $http->setLastModified($cached_feed->last_modified());
                }
            }
        }
        
        // try to fetch feed
        
        $http_success = $http->fetch($url);
        
        if (!$http_success) {
            $errormsg = $this->http_error_msg($http);
            $this->error($errormsg);
            
            // failed to fetch feed, returning stale feed from cache
            
            if($cached_feed and !$options['cache_fresh_only']) {
                return $cached_feed;
            }
            return false;
        }
        
        // conditional GET - content unchanged 
        if ($cached_feed and $http->isUnchanged() ) {
            // reset cache on 304 (at minutillo insistent prodding)
            $cache->set($cache_key, $cached_feed);
            return $cached_feed;
        }
        elseif($http->isSuccess() ) {
            $p = $this->parser($options);
            $status = $p->parse($http->body);
            if (!$status) {
                $this->error($p->error['msg']);
                return false;
            }
            $feed = new Magpie_Feed();
            $feed->from_parser($p);
            $feed->set_headers( $http->headers );
            if ($cache) {
                $cache->set( $cache_key, $feed );
            }
            return $feed;
        }
    
        $this->error('Unable to retrieve RSS feed for unknown reasons: $url');
        return false;
    }
    
    
    
    function http_error_msg($http) {
        $errormsg = "Failed to fetch: " . $http->_url;
        if ( $http->isTimeout() ) {
            $errormsg .= "(Request timed out after " . $http->timeout() . " seconds)";
        }
        elseif ( $http->is404() ) {
            $errormsg = "Feed not found: " . $http->_url;
        }
        elseif ( $http->isClientError() or $http->error ) {
            $http_error = substr($resp->error, 0, -2); 
            $errormsg .= "(HTTP Error: $http_error)";
        }
        else {
            $errormsg .=  "(HTTP Response: " . $resp->status .')';
        }
        return $errormsg;
    }
     
    /**
        merge per feed options with defaults
    */
    function options($options) {
        $possible_options = array(
            'output_encoding', 'input_encoding', 'detect_encoding',
            'cache_on', 'cache_dir', 'cache_age', 'cache_fresh_only',
            'http_timeout', 'http_user_agent', 'http_use_gzip', 'magpie_debug',
            'http_user_agent_no_cache'
        );
        
        $merged_options = array();
        foreach ($possible_options as $opt) {
            $merged_options[$opt] = isset($options[$opt]) ? $options[$opt] : $this->$opt;
        }
        
        return $merged_options;
    }
    
    function init_defaults() {
        
        // charset
        
        $this->output_encoding = $this->default_setting('MAGPIE_OUTPUT_ENCODING', 'utf-8');
        $this->input_encoding = $this->default_setting('MAGPIE_INPUT_ENCODING', null);
        $this->detect_encoding = $this->default_setting('MAGPIE_DETECT_ENCODING', true);
        
        // configure cache
        
        $this->cache_on     = $this->default_setting('MAGPIE_CACHE_ON', true);
        $this->cache_dir    = $this->default_setting('MAGPIE_CACHE_DIR', null); // defer to cache lib
        $this->cache_age    = $this->default_setting('MAGPIE_CACHE_AGE', null); // defer to cache lib
        $this->cache_fresh_only = $this->default_setting('MAGPIE_CACHE_FRESH_ONLY', false);
        
        // configure http agent
        
        $default_agent = 'MagpieRSS/' . $this->VERSION . ' (+http://magpierss.sf.net)';
        $this->http_user_agent = $this->default_setting('MAGPIE_USER_AGENT', $default_agent);
        $no_cache_agent = substr($default_agent, 0, -1) . '; No Cache)';
        
        $this->http_user_agent_no_cache =  
            $this->default_setting('MAGPIE_USER_AGENT_NO_CACHE', $no_cache_agent);
        
        $this->http_timeout = $this->default_setting('MAGPIE_FETCH_TIME_OUT', 5);
        
        // attempt to use gzip if neccessary libs present?
        $this->http_use_gzip = $this->default_setting('MAGPIE_USE_GZIP', true);
        
        //

        $this->magpie_debug = $this->default_setting('MAGPIE_DEBUG', 0);
    }
    
    function default_setting($field, $default_value) {
        if (defined($field)) {
            return constant($field);
        }
        else {
            return $default_value;
        }
    }
    
    function parser($options) {
        if ($this->parser) {
            return $this->parser;
        }
        else {
            $class = $this->loadLib($this->PARSER_CLASS);
            return new $class($options['output_encoding']);
        }
    }
    
    function http($options) {
        $class = $this->loadLib($this->HTTP_CLASS);
        $http = new $class;
        $http->setAgent($options['http_user_agent']);
        $http->_connect_timeout = $options['http_timeout'];
        $http->__accept_gzip = $options['http_use_gzip'];
        
        return $http;
    }
    
    function cache($options) {
        $class = $this->loadLib($this->CACHE_CLASS);
        
        return new $class($this->cache_dir, $this->cache_age);
    }
    
    // is using custom class, make sure its load before instantiating Magpie
    function loadLib($class) {
        if(class_exists($class)) {
            return $class;
        }
        else {
            require_once(dirname(__FILE__) . '/' . strtolower($class) . '.php');   
        }
        return $class;        
    }
    
    function error ($errormsg, $lvl=E_USER_ERROR) {
        // append PHP's error message if track_errors enabled
        if ( isset($php_errormsg) ) { 
            $errormsg .= " ($php_errormsg)";
        }
        
        if ($errormsg) {
            $errormsg = "Magpie: $errormsg";
        
            $this->_magpie_error = $errormsg;
            
            if ($this->trigger_errors) {
                trigger_error( $errormsg, $lvl);
            }
        }
    
        return $this->_magpie_error;
    }
    
} // end Magpie

class Magpie_Feed {
    # a simple data object
    
    var $items          = array();  // collection of parsed items
    var $feed           = array();
    var $feed_type;
    var $feed_version;

    var $_namespaces     = array();
    
    var $from_cache     = false;
    
    var $_headers        = array();
    var $_etag           = false;
    var $_last_modified  = false;
    
    var $output_encoding;
    
    function items() {
        return $this->items;
    }
    
    function entries() {
        return $this->items();
    }
    
    function from_parser($parser) {
        $this->items = $parser->items;
        $this->feed  = $parser->feed;
        $this->channel = $this->feed;
        $this->feed_type = $parser->feed_type;
        $this->feed_version = $parser->feed_version;
        $this->_namespaces = $parser->namespaces;
        $this->output_encoding = $parser->output_encoding;
    }
    
    /**
        set http headers on successful fetch
    */
    function set_headers($headers) {
        $this->_headers = $headers;
        if(isset($headers['etag'])) {
            $this->_etag = $headers['etag'];
        }
        
        if (isset($headers['last-modified'])) {
            $this->_last_modified = $headers['last-modified'];
        }
    }
    
    function etag() {
        $this->_etag;
    }
    
    function last_modified() {
        $this->__last_modified;
    }
}

?>
