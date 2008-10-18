<?php
/*
Plugin Name: (Word)Press It
Plugin URI: http://getlilina.org/docs/plugins:wordpress_it
Description: Adds a Press It link to each entry to post to a WordPress blog
Author: Ryan McCue
Version: 1.0
Min Version: 1.0
Author URI: http://cubegames.net
License: GPL
*/
function pressit_js() {
?>
<script type="text/javascript">
	$(document).ready(function() {
		$(".pressit_button").click(function() {
			console.log($(this).attr('href'));
			var result = window.open($(this).attr('href'),'t','toolbar=0,resizable=0,scrollbars=1,status=1,width=700,height=500');
			if(!result)
				return true;
			return false;
		});
	});
</script>
<?php
}

function pressit_options() {
	global $wp_url;
	$wp_url = new DataHandler();
	$wp_url = $wp_url->load('pressit.data');
	$wp_url = 'http://<MY WORDPRESS DOMAIN>/wp-admin/bookmarklet-advanced.php?&popupurl=%1$s&popuptitle=%2$s';
	$wp_url = 'http://getlilina.org/wordpress/wp-admin/press-this.php?u=%1$s&t=%2$s&s=%3$s';
}
/**
 * Display a notice to the users why the styles are disabled
 */
function pressit_button($actions) {
	global $wp_url;
	$press_url = sprintf($wp_url, urlencode(get_the_link()), urlencode(get_the_title()), urlencode(get_the_summary()));
	$actions[] = '<a href="' . $press_url  . '" class="pressit_button">Press It</a>';
	return $actions;
}

add_action('init', 'pressit_options');
add_action('template_header', 'pressit_js');
add_filter('action_bar', 'pressit_button');
?>