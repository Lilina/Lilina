<?php
//STOP RIGHT THERE!
//Instead of dynamically generating the rest,
//we'll use a cached version
//From http://www.ilovejackdaniels.com/php/caching-output-in-php/
function checkCached(){
	$cachefile = $settings['cachedir'] . md5('index') . '.html'; // Cache file to either load or create
	$cachefile_created = ((@file_exists($cachefile))) ? @filemtime($cachefile) : 0;
	clearstatcache();
	// Show file from cache if still valid
	if (time() - $settings['cachetime'] < $cachefile_created) {
		//ob_start('ob_gzhandler');
		readfile($cachefile);
		//ob_end_flush();
		exit();
	}
	// If we're still here, we need to generate a cache file
	ob_start();
}
?>