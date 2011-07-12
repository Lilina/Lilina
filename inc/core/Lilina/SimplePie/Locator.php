<?php

class Lilina_SimplePie_Locator extends SimplePie_Locator
{
	protected $document = false;

	public function __construct(&$file, $timeout = 10, $useragent = null, $file_class = 'SimplePie_File', $max_checked_feeds = 10, $content_type_sniffer_class = 'SimplePie_Content_Type_Sniffer')
	{
		parent::__construct($file, $timeout, $useragent, $file_class, $max_checked_feeds, $content_type_sniffer_class);

		$old = libxml_use_internal_errors(true);
		if (!$this->is_feed($this->file)) {
			$this->document = new DOMDocument();
			$this->document->loadHTML($this->file->body);
		}

		libxml_use_internal_errors($old);
	}

	public function find($type = SIMPLEPIE_LOCATOR_ALL, &$working)
	{
		$result = parent::find($type, $working);
		if (is_array($result) && isset($result[0]['title'])) {
			return $result['file'];
		}
	}

	public function get_base()
	{
		$this->http_base = $this->file->url;
		$this->base = $this->http_base;
		$elements = $this->get_element('base');
		foreach ($elements as $element)
		{
			if ($element['attribs']['href']['data'] !== '')
			{
				$this->base = SimplePie_Misc::absolutize_url(trim($element['attribs']['href']['data']), $this->http_base);
				$this->base_location = $element['offset'];
				break;
			}
		}
	}

	public function autodiscovery()
	{
		$links = array_merge($this->get_element('link'), $this->get_element('a'), $this->get_element('area'));
		$done = array();
		$feeds = array();
		foreach ($links as $link)
		{
			if ($this->checked_feeds === $this->max_checked_feeds)
			{
				break;
			}
			if (isset($link['attribs']['href']['data']) && isset($link['attribs']['rel']['data']))
			{
				$rel = array_unique(SimplePie_Misc::space_seperated_tokens(strtolower($link['attribs']['rel']['data'])));

				if ($this->base_location < $link['offset'])
				{
					$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->base);
				}
				else
				{
					$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->http_base);
				}

				if (!in_array($href, $done) && in_array('feed', $rel) || (in_array('alternate', $rel) && !empty($link['attribs']['type']['data']) && in_array(strtolower(SimplePie_Misc::parse_mime($link['attribs']['type']['data'])), array('application/rss+xml', 'application/atom+xml'))) && !isset($feeds[$href]))
				{
					$this->checked_feeds++;
					$headers = array(
						'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
					);
					$feed = new $this->file_class($href, $this->timeout, 5, $headers, $this->useragent);
					if ($feed->success && ($feed->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($feed->status_code === 200 || $feed->status_code > 206 && $feed->status_code < 300)) && $this->is_feed($feed))
					{
						$feeds[$href] = array(
							'file' => $feed,
							'title' => false
						);
						if (!empty($link['attribs']['title']['data']))
							$feeds[$href]['title'] = trim($link['attribs']['title']['data']);
					}
				}
				$done[] = $href;
			}
		}

		if (!empty($feeds))
		{
			return array_values($feeds);
		}
		else {
			return null;
		}
	}

	public function get_links()
	{
		$links = $this->get_element('a');
		foreach ($links as $link)
		{
			if (isset($link['attribs']['href']['data']))
			{
				$href = trim($link['attribs']['href']['data']);
				$parsed = SimplePie_Misc::parse_url($href);
				if ($parsed['scheme'] === '' || preg_match('/^(http(s)|feed)?$/i', $parsed['scheme']))
				{
					if ($this->base_location < $link['offset'])
					{
						$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->base);
					}
					else
					{
						$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->http_base);
					}

					$current = SimplePie_Misc::parse_url($this->file->url);

					if ($parsed['authority'] === '' || $parsed['authority'] === $current['authority'])
					{
						$this->local[] = $href;
					}
					else
					{
						$this->elsewhere[] = $href;
					}
				}
			}
		}
		$this->local = array_unique($this->local);
		$this->elsewhere = array_unique($this->elsewhere);
		if (!empty($this->local) || !empty($this->elsewhere))
		{
			return true;
		}
		return null;
	}

	protected function get_element($realname)
 	{
 		if ($this->document === false)
 		{
 			return SimplePie_Misc::get_element($realname, $this->file->body);
 		}
 		static $offset = 0;
 		$return = array();
		$elems = $this->document->getElementsByTagName($realname);
		if ($elems->length > 0)
 		{
			for ($i = 0; $i < $elems->length; $i++)
 			{
				$elem = $elems->item($i);
				$return[$i] = array();
 				$return[$i]['tag'] = $realname;
 				$return[$i]['offset'] = $offset++;
				$return[$i]['full'] = $elem->ownerDocument->saveXML($elem);

				$return[$i]['content'] = '';
				if (count($elem->childNodes) > 0)
 				{
 					$return[$i]['self_closing'] = false;
					foreach ($elem->childNodes as $child)
					{
						$return[$i]['content'] .= $child->ownerDocument->saveXML($child); 
					}
 				}
				else {
					$return[$i]['self_closing'] = true;
				}

 				$return[$i]['attribs'] = array();
				if ($elem->attributes !== null)
 				{
					foreach ($elem->attributes as $name => $node)
 					{
						if (!isset($return[$i]['attribs'][$name]))
 						{
							$return[$i]['attribs'][$name] = array();
 						}
						$return[$i]['attribs'][$name]['data'] = $node->value;
 					}
 				}
 			}
		}
		return $return;
	}
}