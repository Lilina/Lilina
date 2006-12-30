<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		parseopml.php
Purpose:	OPML Parser
Notes:		Adapted from
	http://www.sencer.de/code/showOPML.phps
			CAUTION: HERE BE DRAGONS!
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
error_reporting(E_ALL);
class OPMLParser {
	var $depth			= 0;
	var $parsed_feeds	= array();
	function startElement($parser, $tagName, $attrs) {
		$this -> depth++;
		if ($tagName !== 'OUTLINE') return;
		extract($attrs);
		//print_r($attrs);
		$this -> parsed_feeds[]	= $attrs;
		/*if (!isset($URL)) {
		echo str_repeat(' ',$this->depth).'<li>'.$TITLE."<ul>\r\n";
		}
		else {
			echo str_repeat(' ',$this->depth).'<li><a href="'.($URL).'">'.utf8_encode($TITLE).'</a>';
			if (isset($XMLURL) and $XMLURL != '')
				echo ' [<a href="'.htmlentities($XMLURL).'">xml</a>]';
			echo "</li>\r\n";
		}*/
	}
    
	function endElement($parser, $tagName) {
		$this -> depth--;
		if ($tagName !== 'OUTLINE') return;
		static $lastdepth;
		if (!isset($lastdepth)) $lastdepth = 0;
		if ($this->depth == $lastdepth) return;
		if ($this->depth > $lastdepth)
		{
			$lastdepth = $this->depth;
		}
		else
		{
			echo str_repeat(' ',$this->depth)."</ul></li>\r\n";
			$lastdepth--;
		}
	}
	function characterData($parser, $data)
	{
    
	}
}

function parse_opml($remote_file) {
	// Create an XML parser
	$xml_parser = xml_parser_create('ISO-8859-1');

	$opml_parser = new OPMLParser();
	xml_set_object($xml_parser,$opml_parser);

	// Set the functions to handle opening and closing tags
	xml_set_element_handler($xml_parser, "startElement", "endElement");

	// Set the function to handle blocks of character data
	xml_set_character_data_handler($xml_parser, "characterData");


	// Open the XML file for reading
	$fp = fopen($remote_file,'r');

	// Read the XML file 4KB at a time
	while ($data = fread($fp, 4096))
		// Parse each 4KB chunk with the XML parser created above
		xml_parse($xml_parser, $data, feof($fp))
			// Handle errors in parsing
			or die(sprintf("XML error: %s at line %d",  
				xml_error_string(xml_get_error_code($xml_parser)),  
				xml_get_current_line_number($xml_parser)));

	// Free up memory used by the XML parser
	xml_parser_free($xml_parser);

	// Close the XML file
	fclose($fp);

	return($opml_parser->parsed_feeds);
}
?> 