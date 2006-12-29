<?php 


class Magpie_Parser {
    
    var $parser;
    
    var $items          = array();  // collection of parsed items
    var $feed           = array();
    var $feed_type;
    var $feed_version;

    var $namespaces     = array();
    
    var $error          = array();
    
    // track internal state of the parser
    var $stack              = array(); // parser stack
    var $contexts           = array(); // context stack
    var $infeed             = false;
    var $initem             = false;
    var $incontent          = false;
    
    var $baseuri            = '';
    var $basestack          = array();
    var $content_attrs      = array();
    
    var $debug              = true;
    
    function Magpie_Parser($output_encoding='utf-8') {
        # encodings, and other stuff get set
        $this->output_encoding = $output_encoding;
    }
    
    
    function parse($source) {
        list($in_enc, $source) = $this->convert_to_utf8($source);
        if(!$this->parser) {
            $this->_init_parser($in_enc, $this->output_encoding);
        }
        $status = xml_parse( $this->parser, $source );
        if (! $status ) {
            $error_code = xml_get_error_code( $this->parser );
            if (  $error_code != XML_ERROR_NONE ) {
                $this->error['msg'] = sprintf("%s at line %d, column %d", 
                        xml_error_string($error_code),
                        xml_get_current_line_number($this->parser),
                        xml_get_current_column_number($this->parser) );
                $this->error['code'] = $error_code;        
            }
        }
        
        xml_parser_free($this->parser);
        $this->channel = $this->feed;
        $this->parser = false;
    
        return $status;
    }
    
    /**
        map prefix + element name => handler function
        note, this is only for elements which require special logic of some sort
    */
    
    var $ELEMENT_HANDLERS = array(
        'item' => array('_start_item', '_end_item'),
        'entry' => array('_start_item', '_end_item'),
        'feed' => array('_start_feed', '_end_feed'),
        'channel' => array('_start_feed', '_end_feed'),
        'link' => array('_start_link', '_end_link'),
        'author' => array('_start_author', '_end_author'),
        'content' => array('_start_content', '_end_content'),
        'content:encoded' => array('_start_content_encoded', '_end_content'),
        'category' => array('', '_end_category'),
        'enclosure' => array('', '_end_enclosure'),
        'guid' => array('', '_end_guid'),
        'cloud' => array('start_just_attributes'),
        'admin:generatoragent' => array('start_rdf_resource_is_payload', null),
        'admin:errorreportsto' => array('start_rdf_resource_is_payload', null),
        'title'  => array('start_content_construct_el', 'end_content_construct_el'),
        'tagline' => array('start_content_construct_el', 'end_content_construct_el')
        // 'dc_date' => array('_start_dc_date', '_end_dc_date'),
        // 'admin_generatoragent' => array('_start_admin_generatoragent', '_end_admin_generatoragent')
    );
    
    function _init_parser($in_enc, $out_enc) {
        $this->parser =xml_parser_create_ns($in_enc);
        xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $out_enc);
        xml_set_object( $this->parser, $this );
        xml_set_element_handler($this->parser, 'start_element_handler', 'end_element_handler');
        xml_set_character_data_handler($this->parser,'character_data_handler');
        xml_set_start_namespace_decl_handler($this->parser, 'start_namespace_handler');
        #xml_set_end_namespace_decl_handler($this->parser, 'end_namespace_handler');
    }
    
    function start_element_handler($parser, $qname, $attrs) {
        $qname = strtolower($qname);        
        $attrs = array_change_key_case($attrs, CASE_LOWER);
        
        // if incontent, treat as char data
        if ($this->incontent) {
            return $this->handle_incontent('start', $qname, $attrs);
        }
        // else
            
        if ($func = $this->get_handler('start', $qname)) {
            $this->$func($qname, $attrs);
        }
        else {
            $this->default_start_element_handler($qname, $attrs);
        }
    }
    
    function end_element_handler($parser, $qname) {
        $qname = strtolower($qname);
        
        // treat as char data
        if ($this->incontent and $this->incontent != $qname) {
            return $this->handle_incontent('end', $qname);
        }
        // else
                        
        if ($func = $this->get_handler('end', $qname)) {
            $this->$func($qname);
        }
        else {
            $this->default_end_element_handler($qname);
        }
        
        // NEED TO HANDLE incontent
        
    }
    
    function default_start_element_handler($qname, $attrs) {
        $this->push_element($qname, $attrs);
    }
        
    function default_end_element_handler($qname) {  
        list($qname, $attrs, $text) = $this->pop_element($qname);
        
        $el_key = $this->qname_to_element_key($qname);
        
        $this->set_content($el_key, $text);
    }
    
    function character_data_handler($parser, $data) {
        if ($this->incontent and $this->is_xhtml($this->content_attrs)) {
            $data = $this->xml_escape($data);
        }

        $this->push_data($data);
    }
    
    function start_namespace_handler($paser, $prefix, $uri) {
        $prefix = strtolower($prefix);
        $uri = strtolower($uri);
        
        if(isset($this->KNOWN_NAMESPACES[$uri])) {
            $this->namespaces[$uri] = $this->KNOWN_NAMESPACES[$uri];
        }
        // prefix not already in use?
        elseif(!in_array($prefix, $this->KNOWN_NAMESPACES)) {
            $this->namespaces[$uri] = $prefix;
        }
        else {
            $this->namespaces[$uri] = $uri;
        }
        
        if (!($this->feed_type and $this->feed_version)) {
            if ($uri === 'http://purl.org/rss/1.0/') {
                $this->feed_type = 'RSS';
                $this->feed_version = '1.0';
            }
            elseif($uri === 'http://www.w3.org/2005/atom') {
                $this->feed_type = 'Atom';
                $this->feed_version = '1.0';
            }
            elseif($uri == 'http://purl.org/atom/ns#') {
                $this->feed_type = 'Atom';
                $this->feed_version = '0.3';
            }
        }
    }
    
    function _start_item($qname, $attrs) {
        $this->initem = true;
        $this->items[] = array();
        $this->push_context($this->items[array_last($this->items)]);
        if (isset($attrs['http://www.w3.org/1999/02/22-rdf-syntax-ns#:about'])) {
            $this->set_content('id', $attrs['http://www.w3.org/1999/02/22-rdf-syntax-ns#:about']);
        }
    }
    
    function _end_item($qname) {
        $this->initem = false;
        $this->after_item($this->current_item());
        $this->pop_context();
    }
    
    function _start_content($qname, $attrs) {
        $this->handle_start_content_construct($qname, $attrs);
    }
    
    function _start_content_encoded($qname, $attrs) {
        $attrs['type'] = 'html';
        $this->handle_start_content_construct($qname, $attrs);
    }
         
    function _end_content($qname) {        
        $content = $this->handle_end_content_construct($qname);    
        $this->set_content('content', $content['value']);
        $this->set_content('content_detail', $content);
    }
    
    // function _start_title($qname, $attrs) {
    //     $this->start_content_construct($qname, $attrs);
    // }
    //     
    // function _end_title($qname) {
    //     $content = $this->end_content_construct($qname);    
    //     $this->set_content('title', $content['value']);
    //     $this->set_content('title_detail', $content);        
    // }
        
    function _start_feed($qname, $attrs) {
        $this->infeed = true;
        $this->push_context($this->feed);
    }
    
    function _end_feed($qname) {
        $this->infeed = false;
        $this->pop_context();
    }
    
    function _start_link($qname, $attrs) {
        $this->push_element($qname, $attrs);
    }
    
    function _end_link($qname) {
        list($qname, $attrs, $text) = $this->pop_element($qname);
        
        # set default
        $link = array_merge(array('rel' => 'alternate'), $attrs);
        if(!isset($link['href']) and $text) {
            $link['href'] = $text;
        }
        
        if($link['rel'] == 'alternate' and $link['href']) {
            $this->set_content('link', $link['href']); // only set first time called
        }
        else {
            $this->set_content('link_'.$link['rel'], $link['href']);
        }
        
        $this->set_content('links', $link, 'append');
        $this->after_link($link);   
    }
    
    function _end_guid($qname) {
        list($qname, $attrs, $text) = $this->pop_element($qname);
        
        $this->set_content('guid', $text);
        if(isset($attrs['ispermalink'])) {
            $this->set_content('guid_ispermalink', $attrs['ispermalink']);
        }
    }
    
    function _end_category($qname) {
        list($qname, $attrs, $text) = $this->pop_element($qname);
        
        $category = array();
        $category['scheme'] = array_get($attrs, 'scheme',
            array_get($attrs, 'domain', '')
        );
        $category['domain'] = $category['scheme'];
        $category['label'] = array_get($attrs, 'label', '');
        if($term = array_get($attrs, 'term')) {
            $category['term'] = $term;
        }
        elseif($text) {
            $category['term'] = $text;
        }
        
        $this->set_content('category', $category['term']);
        $this->set_content('categories', $category, 'append');
    }
    
    function _end_enclosure($qname) {
        list($qname, $attrs, $text) = $this->pop_element($qname);
        
        $this->set_content('enclosures', $attrs, 'append');
    }
    
    function _start_author($qname, $attrs) {
        if($this->feed_type == 'Atom') {
            $this->start_person_construct($qname, $attrs);
        }
        else {
            $this->default_start_element_handler($qname, $attrs);
        }

    }
    
    function _end_author($qname) {
        if($this->feed_type == 'Atom') {
            $person = $this->end_person_construct($qname);
            $this->set_content('author_detail', $person);
            $this->set_content('author', sprintf("%s (%s)", $person['name'], $person['email']));
        }
        else {
            $this->default_end_element_handler($qname);
        }
    }
    
    /**
      generic handler for elements with just attributes
    
    */
    function start_just_attributes($qname, $attrs) {
        $el_key = $this->qname_to_element_key($qname);
        $this->set_content($el_key, $attrs);
    }
    
    function start_rdf_resource_is_payload($qname, $attrs) {
        $el_key = $this->qname_to_element_key($qname);
        if (isset($attrs[$this->RDF . ':resource'])) {
            $this->set_content($el_key, $attrs[$this->RDF . ':resource']);
        }
    }
    
    function start_content_construct_el($qname, $attrs) {
        $this->handle_start_content_construct($qname, $attrs);
    }
    
    function end_content_construct_el($qname) {
        $content = $this->handle_end_content_construct($qname);
        $el_key = $this->qname_to_element_key($qname);
        $this->set_content($el_key, $content['value']);
        $detail_key = is_array($el_key) ?
            array($el_key[0], $el_key[1].'_detail') :
            $el_key.'_detail';
        $this->set_content($detail_key, $content);
    }
    
    function handle_start_content_construct($qname, $attrs) {
        $this->incontent = $qname;
        $this->push_element($qname, $attrs);
        $this->content_attrs = array_merge(
            array('type' => 'text'),
            $attrs
        );
    }
    
    function handle_end_content_construct($qname) {
        if ($this->incontent and $this->incontent == $qname) {
            $this->incontent = false;
            list($qname, $attrs, $text) = $this->pop_element($qname);
            $content_construct = $this->content_attrs;
            $this->content_attrs = array();
            
            if ($this->is_base64($content_construct)) {
                $content_construct['value'] = base64_decode($text);
            }
            else {
                $content_construct['value'] = $text;
            }
            return $content_construct;
        }
    }
    
    function start_person_construct($qname, $attrs) {
        $this->inperson = 1;
        
        $person = array( 'name'  => null, 'uri'   => null, 'email' => null);
        $this->push_context($person);
    }
    
    function end_person_construct($qname) {
        $this->inperson = 0;
        
        $person = $this->pop_context();
        return $person;
    }
    
    function after_item(&$item) {
        # post processing logic goes here
        # normalization, and such
        # copy in Atom style inherited info
        
        if (!isset($item['link']) and 
            isset($item['guid']) and
            isset($item['guid_ispermalink'])) 
        {
            $item['link'] = $item['guid'];
        }
        
        if(!isset($item['guid']) and isset($item['id'])) {
            $item['guid'] = $item['id'];
        }
    }
    
    function after_link($link) {
        if ($link['rel'] == 'enclosure') {
            $link['url'] = $link['href'];
            $this->set_content('enclosures', $link, 'append');
        }
    }
    
    # XXX: track stack of content arrays|stack of contexts
    #  also $key should be arbitrary depth
    #
    function set_content($key, $value, $append=false) {
        
        // $context = null;
//      if ($this->initem and count($this->items)) {
//          $context =& $this->items[array_last($this->items)];
//      }
//      elseif ($this->infeed) {
//          $context =& $this->feed;
//      }
        
        $context =& $this->current_context();
        // echo "CONTEXT: "; print_r($context);
        // echo "KEY: "; print_r($key);
        // echo "VALUE: "; print_r($value);
        
        if (isset($context)) {
            if ($append) {
                marray_append($context, $key, $value);
            }
            elseif(!marray_isset($context, $key)) {
                marray_set($context, $key, $value);
            }
        }
    }
    
    function push_context(&$context) {
        $context = ($context) ? $context : array();
        
        $this->contexts[] =& $context;
    }
     
    function pop_context() {
        return array_pop($this->contexts);
    }
    
    function &current_context() {
        return $this->contexts[array_last($this->contexts)];
    }
    
    function &current_item() {
        return $this->items[array_last($this->items)];
    }
    
    /**
        does element have custom parsing logic?
        
        return a callable to handle this element. 
    */
    function get_handler($stage, $qname) {
        list($uri, $name) = $this->split_qname($qname);
        $prefix = $this->ns_uri_to_prefix($uri);
        if($prefix) {
            $key = "${prefix}:${name}";
        }
        else {
            $key = $name;
        }
        
        if (isset($this->ELEMENT_HANDLERS[$key])) {
            # assume callable is a method of this parser object.
            # TODO: need a syntax for arbitrary method names
            switch($stage) {
                case 'start':
                    $stage = 0;
                    break;
                case 'end':
                    $stage = 1;
                    break;
                case 'after':
                    $stage = 2;
                    break;
            }
            
            if(isset($this->ELEMENT_HANDLERS[$key][$stage])) {
                $callable = $this->ELEMENT_HANDLERS[$key][$stage];
                if (is_callable(array($this, $callable))) {
                    return $callable;
                }
                // else {
 //                    return 'null_op';
 //                }
            }
        }
        # else
        return false;
    }
    
    function set_handlers($uri, $name, $handlers) {
        $prefx = $this->ns_uri_to_prefix($uri);
        if(isset($prefix)) {
            $key = "${prefix}_${name}";
        }
        else {
            $key = $name;
        }
        # XXX: test callability
        $this->ELEMENT_HANDLERS[$key] = $handlers;
    }

    
    #
    # track parse stack
    #
    
    
    function push_element($qname, $attrs=array()) {
        $el = array();
        $el[_MAGPIE_STACK_NAME] = $qname;
        $el[_MAGPIE_STACK_ATTRS] = $attrs;
        $el[_MAGPIE_STACK_BUFFER] = array();
        $this->stack[] = $el;
    }
    
    function handle_incontent($mode, $qname, $attrs=null) {
        if ($mode=='start') {
            $attrs_str = '';
            if (count($attrs)) {
                $attrs_str = join(' ', 
                    array_map('_magpie_map_attrs', 
                    array_keys($attrs), 
                    array_values($attrs) ) 
                );    
            }
            $el_key = $this->qname_to_element_key($qname);
            $el = is_array($el_key) ? $el_key[1]: $el_key;
            $data = sprintf('<%s%s>', $el, $attrs_str);
        }
        else {
            $el_key = $this->qname_to_element_key($qname);
            $el = is_array($el_key) ? $el_key[1]: $el_key;
            $last_data = $this->rewind_data();
            
            # close found immediately after open?
            if(strstr($last_data, "<$el")) {
                $data = preg_replace('/>$/', ' />', $last_data);
            }
            else {
                $this->push_data($last_data);
                $data = sprintf('</%s>', $el);
            }
        }
        
        $this->push_data($data);        
    }
    
    function push_data($data) {
        $this->stack[array_last($this->stack)][_MAGPIE_STACK_BUFFER][] = $data; 
    }
    
    function rewind_data() {
        return array_pop($this->stack[array_last($this->stack)][_MAGPIE_STACK_BUFFER]);
    }
    
    /**
      return array($qname, $attrs, $joined_data)
    */
    function pop_element($qname) {
        if ($this->stack[array_last($this->stack)][_MAGPIE_STACK_NAME] !== $qname) {
            return false;
        }
        
        $last = array_pop($this->stack);
        $text = implode('', $last[_MAGPIE_STACK_BUFFER]);
        return array($last[_MAGPIE_STACK_NAME], 
            $last[_MAGPIE_STACK_ATTRS],
            $text); 
    }
    
    #
    # update or override known namespaces (uri to prefix mapping)
    #
    
    // function add_namespace($uri, $prefix) {
    //  $KNOWN_NAMESPACES[$uri] => $prefix;
    // }
        
    // function ns_uri_to_prefix($uri) {
    //     if(isset($this->KNOWN_NAMESPACES[$uri])) {
    //         return $this->KNOWN_NAMESPACES[$uri];
    //     }
    //     else {
    //         # lookup declared prefixes
    //         # return $declared[$uri];
    //         return $uri;
    //     }
    // }
    
    function ns_uri_to_prefix($uri) {
        if (isset($this->namespaces[$uri])) {
            return $this->namespaces[$uri];
        }
        else {
            return $uri;
        }
    }
    
    function split_qname($qname) {
        $uri = '';
        if (strpos($qname, ':') !== false) {
            $split = strrpos($qname, ':');
            $uri = substr($qname, 0, $split);
            $name = substr($qname, $split+1);
        }
        else {
            $name = $qname;
        }
        return array($uri, $name);
    }
    
    /**
      return $name if in default namespace, otherwise array($prefix, $name)
    
    */
    function qname_to_element_key($qname) {
        list($uri, $name) = $this->split_qname($qname);
        $prefix = $this->ns_uri_to_prefix($uri);
        if ($prefix) {
            return array($prefix, $name);
        }
        else {
            return $name;
        }
    }
    
    /**
      attempt to cast data to utf8 before parsing
      
      @return array($encoding, $encoded_data)
    */
    function convert_to_utf8($source) {
        $enc = $this->find_encoding($source);
        
        if ($enc) {
            if (function_exists('iconv'))  {
                if (iconv($enc, 'UTF-8', $source)) {
                    $encoded_source = iconv($enc, 'UTF-8//TRANSLIT', $source);
                    return array('UTF-8', $encoded_source);
                }
            }
        
            # iconv isn't loaded, or failed
            if(function_exists('mb_convert_encoding')) {
                $encoded_source = mb_convert_encoding($source, 'UTF-8', $enc );
                if ($encoded_source) {
                     return array('UTF-8', $encoded_source);
                }
            }
        }
        
        # else failed to convert
        return array($enc, $source);
    }
    
    function find_encoding($source) {
        if (preg_match('/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m', $source, $m)) {
            $in_enc = strtoupper($m[1]);
            return $in_enc;
        }
        elseif($this->is_php4()) {
            return 'UTF-8';
        }
        else {
            # under php5 allow libxml to guess
            return '';
        }
    }
    
    function is_php4() {
        if ( substr(phpversion(),0,1) == 4) {
            return true;
        }
        else {
            return false;
        }
    }
    
    function is_php5() {
        if ( substr(phpversion(),0,1) == 5) {
            return true;
        }
        else {
            return false;
        }
    }
        
    
    #
    # common namespace, and prefix mappings, from feedparser.py
    #
    
    var $KNOWN_NAMESPACES = array(
        ''                                              =>'',   
        'http://backend.userland.com/rss'               =>'',
        'http://blogs.law.harvard.edu/tech/rss'         =>'',
        'http://purl.org/rss/1.0/'                      =>'',
        'http://my.netscape.com/rdf/simple/0.9/'        =>'',
        'http://example.com/newformat#'                 =>'',
        'http://example.com/necho'                      =>'',
        'http://purl.org/echo/'                         =>'',
        'uri/of/echo/namespace#'                        =>'',
        'http://purl.org/pie/'                          =>'',
        'http://purl.org/atom/ns#'                      =>'',
        'http://www.w3.org/2005/atom'                   =>'',
        'http://purl.org/rss/1.0/modules/rss091#'       =>'',

        'http://webns.net/mvcb/'                        => 'admin',
        'http://purl.org/rss/1.0/modules/aggregation/'  => 'ag',
        'http://purl.org/rss/1.0/modules/annotate/'     => 'annotate',
        'http://media.tangent.org/rss/1.0/'             => 'audio',
        'http://backend.userland.com/blogchannelmodule' => 'blogChannel',
        'http://web.resource.org/cc/'                   => 'cc',
        'http://backend.userland.com/creativecommonsrssmodule' =>'creativeCommons',
        'http://purl.org/rss/1.0/modules/company'       => 'co',
        'http://purl.org/rss/1.0/modules/content/'      => 'content',
        'http://my.theinfo.org/changed/1.0/rss/'        => 'cp',
        'http://purl.org/dc/elements/1.1/'              => 'dc',
        'http://purl.org/dc/terms/'                     => 'dcterms',
        'http://purl.org/rss/1.0/modules/email/'        => 'email',
        'http://purl.org/rss/1.0/modules/event/'        => 'ev',
        'http://rssnamespace.org/feedburner/ext/1.0'    => 'feedburner',
        'http://freshmeat.net/rss/fm/'                  => 'fm',
        'http://xmlns.com/foaf/0.1/'                    => 'foaf',
        'http://www.w3.org/2003/01/geo/wgs84_pos#'      => 'geo',
        'http://postneo.com/icbm/'                      => 'icbm',
        'http://purl.org/rss/1.0/modules/image/'        => 'image',
        'http://www.itunes.com/DTDs/PodCast-1.0.dtd'    => 'itunes',
        'http://example.com/DTDs/PodCast-1.0.dtd'       => 'itunes',
        'http://purl.org/rss/1.0/modules/link/'         => 'l',
        'http://search.yahoo.com/mrss'                  => 'media',
        'http://madskills.com/public/xml/rss/module/pingback/'  =>'pingback',
        'http://prismstandard.org/namespaces/1.2/basic/'=> 'prism',
        'http://www.w3.org/1999/02/22-rdf-syntax-ns#'   => 'rdf',
        'http://www.w3.org/2000/01/rdf-schema#'         => 'rdfs',
        'http://purl.org/rss/1.0/modules/reference/'    => 'ref',
        'http://purl.org/rss/1.0/modules/richequiv/'    => 'reqv',
        'http://purl.org/rss/1.0/modules/search/'       =>  'search',
        'http://purl.org/rss/1.0/modules/slash/'        =>  'slash',
        'http://schemas.xmlsoap.org/soap/envelope/'     => 'soap',
        'http://purl.org/rss/1.0/modules/servicestatus/' => 'ss',
        'http://hacks.benhammersley.com/rss/streaming/' => 'str',
        'http://purl.org/rss/1.0/modules/subscription/' => 'sub',
        'http://purl.org/rss/1.0/modules/syndication/'  => 'sy',
        'http://purl.org/rss/1.0/modules/taxonomy/'     => 'taxo',
        'http://purl.org/rss/1.0/modules/threading/'    => 'thr',
        'http://purl.org/rss/1.0/modules/textinput/'    => 'ti',
        'http://madskills.com/public/xml/rss/module/trackback/' => 'trackback',
        'http://wellformedweb.org/commentAPI/'          => 'wfw',
        'http://purl.org/rss/1.0/modules/wiki/'         => 'wiki',
        'http://www.w3.org/1999/xhtml'                  => 'xhtml',
        'http://www.w3.org/XML/1998/namespace'          => 'xml',
        'http://schemas.pocketsoap.com/rss/myDescModule/' => 'szf'
    );
    
    var $RDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    
    function is_base64($content) {
        if (isset($content['mode']) and $content['mode'] == 'base64') {
            return true;
        }
        if (!isset($content['type'])) {
            return false;
        }
        if (preg_match('/^(text|html|xhtml)$/iu', $content['type'])) {
            return false;
        }
        if (preg_match('!^text/!iu', $content['type'])) {
            return false;
        }
        if (preg_match('!(\+xml|/xml)$!iu', $content['type'])) {
            return false;
        }
        
        // XXX
        return true;
    }
    
    function is_xhtml($content) {
        if(isset($content['mode']) and $content['mode'] == 'xml') {
            return true;
        }
        
        if(!isset($content['type'])) {
            return false;
        }
        
        if($content['type'] == 'application/xhtml+xml') {
            return true;
        }
        
        if($content['type'] == 'xhtml') {
            return true;
        }
        
        return false;
    }
     
    function xml_escape($data) {
        return htmlspecialchars($data, ENT_NOQUOTES);
    }
    
    // XXX: not the most elegant way of handling this 
    function null_op($foo=null, $quxx=null, $bar=null) {

    }
    
    function debug($str, $lvl=E_USER_NOTICE) {
        if ($this->debug) {
            trigger_error($str, $lvl);
        }
    }
    
    
} // end Magpie_Parser

/**
  CONSTANTS
*/
define('_MAGPIE_STACK_NAME', 0);
define('_MAGPIE_STACK_ATTRS', 1);
define('_MAGPIE_STACK_BUFFER', 2);

/**
 utility methods

*/
function array_last($arr) {
    if (count($arr)) {
        return count($arr)-1;
    }
    else {
        return false;
    }
}

function array_get($arr, $key, $default=null) {
    if(isset($arr[$key])) {
        return $arr[$key];
    }
    else {
        return $default;
    }
}

# patch to support medieval versions of PHP4.1.x, 
# courtesy, Ryan Currie, ryan@digibliss.com

if (!function_exists('array_change_key_case')) {
    define("CASE_UPPER",1);
    define("CASE_LOWER",0);


    function array_change_key_case($array,$case=CASE_LOWER) {
       if ($case==CASE_LOWER) $cmd='strtolower';
       elseif ($case==CASE_UPPER) $cmd='strtoupper';
       foreach($array as $key=>$value) {
               $output[$cmd($key)]=$value;
       }
       return $output;
    }

}


/**

  marray_* for working with arrays that potentially have multi-valued keys

*/

function marray_set(&$arr, $key, $value, $replace=false) {
    if (!marray_isset($arr, $key) or $replace) {
        if (is_array($key)) {
            if (!isset($arr[$key[0]])) {
                $arr[$key[0]] = array();
            }
            $arr[$key[0]][$key[1]] = $value;
        }
        else {
            $arr[$key] = $value;
        }
        return true;
    }
    else {
        return false;
    }
}

function marray_append(&$arr, $key, $new_value) {
    if(!marray_isset($arr, $key)) {
        marray_set($arr, $key, array($new_value));
    }
    else {
        $val = marray_get($arr, $key);
        if(is_array($val)) {
            $val[] = $new_value;
            marray_set($arr, $key, $val, 'replace');
        }
        else {
            return false;
        }
    }
}

function marray_isset(&$arr, $key) {
    if(!is_array($key)) {
        return isset($arr[$key]);
    }
    else {
        if (isset($arr[$key[0]]) and
            isset($arr[$key[0]][$key[1]]) )
        {
            return true;
        }
        else {
            return false;
        }
    }   
}

function marray_get(&$arr, $key) {
    if(!is_array($key)) {
        if (isset($arr[$key])) {
            return $arr[$key];
        }
        else {
            return null;
        }
    }
    elseif(marray_isset($arr, $key)) {
        return $arr[$key[0]][$key[1]];
    }
}


function _magpie_map_attrs($k, $v) {
    return "$k=\"$v\"";
}

    


?>