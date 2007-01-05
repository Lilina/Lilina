<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		ping.php
Purpose:	Sends trackbacks
Notes:		Only used in installation, however
			plugins may like to use
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
function ping($tb, $url, $title = '', $excerpt = '') {
	$response = '';
	$reason = '';
	// Set default values
	if (empty($title)) {
		$title = 'Trackbacking your entry...';
	}
	if (empty($excerpt)) {
		$excerpt = 'I found your entry interesting so I\'ve added a Trackback to it on my weblog :)';
	}
	// Parse the target
	$target = parse_url($tb);
	if ((isset($target['query'])) && ($target['query'] != '')) {
		$target['query'] = '?' . $target['query'];
	} else {
		$target['query'] = '';
	}
	if ((isset($target['port']) && !is_numeric($target['port'])) || (!isset($target['port']))) {
		$target['port'] = 80;
	}
	// Open the socket
	$tb_sock = fsockopen($target['host'], $target['port']);
	// Something didn't work out, return
	if (!is_resource($tb_sock)) {
		return 'Can\'t connect to: ' . $tb . '.';
		exit;
	}
	// Put together the things we want to send
	$tb_send = 'url=' . rawurlencode($url) . '&title=' . rawurlencode($title) . '&blog_name=' . rawurlencode($settings['sitename']) . '&excerpt=' . rawurlencode($excerpt);
	// Send the trackback
	fputs($tb_sock, 'POST ' . $target['path'] . $target['query'] . " HTTP/1.1\r\n");
	fputs($tb_sock, 'Host: ' . $target['host'] . "\r\n");
	fputs($tb_sock, "Content-type: application/x-www-form-urlencoded\r\n");
	fputs($tb_sock, 'Content-length: ' . strlen($tb_send) . "\r\n");
	fputs($tb_sock, "Connection: close\r\n\r\n");
	fputs($tb_sock, $tb_send);
	// Gather result
	while (!feof($tb_sock)) {
		$response .= fgets($tb_sock, 128);
	}
	// Close socket
	fclose($tb_sock);
	// Did the trackback ping work
	strpos($response, '<error>0</error>') ? $return = true : $return = false;
	// send result
	return $return;
}
?>