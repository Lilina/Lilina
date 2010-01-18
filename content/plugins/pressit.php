<?php
/*
Plugin Name: Press It
Plugin URI: http://getlilina.org/
Description: Adds a Press It link to each entry to post to a WordPress blog
Author: Ryan McCue
Version: 1.0
Min Version: 1.0
Author URI: http://ryanmccue.info
License: GPL
*/

// Front-End
/**
 * Add the link to the item actions
 *
 * @param array $actions Previous actions
 * @return array Array with Press It added.
 */
function pressit_button($actions) {
	$wp_url = get_option('pressit_wpurl', '');
	if(empty($wp_url))
		return $actions;

	$wp_url .= 'wp-admin/press-this.php?u=%1$s&t=%2$s&s=%3$s';
	$press_url = sprintf($wp_url, urlencode(get_the_link()), urlencode(get_the_title()), urlencode(get_the_summary()));
	$actions[] = '<a href="' . $press_url  . '" class="pressit_button">Press It</a>';
	return $actions;
}
add_filter('action_bar', 'pressit_button');

/**
 * Javascript to open URL in new window
 */
function pressit_js() {
?>
<script type="text/javascript">
	$(document).ready(function() {
		$(".pressit_button").live("click", function() {
			var result = window.open($(this).attr('href'),'t','toolbar=0,resizable=0,scrollbars=1,status=1,width=700,height=500');
			if(!result)
				return true;
			return false;
		});
	});
</script>
<?php
}
add_action('template_header', 'pressit_js');

// Admin
/**
 * Register our option and associated admin page
 */
function pressit_options() {
	register_option('pressit_wpurl', 'pressit_validate');
	add_option_section('pressit_section', 'Press It Options', 'pressit_section_header');
	add_option_field('pressit_wpurl_field', 'WordPress URL', 'pressit_field', 'pressit_section', array(
		'label_for' => 'pressit_wpurl',
		'note' => 'This should be the same as your "WordPress URL" in your WordPress settings.'
	));
}

/**
 * Header for the option section
 */
function pressit_section_header() {
?>
<p>Press It needs your WordPress URL in order to work properly. This is only used to make the link and isn&apos;t used for any other purposes.</p>
<?php
}

/**
 * Field for the option
 */
function pressit_field() {
?>
<input type="text" name="pressit_wpurl" id="pressit_wpurl" value="<?php echo get_option('pressit_wpurl'); ?>" />
<?php
}

/**
 * Validation callback on POST
 *
 * @param string $value Input value
 * @return string Trailing-slash'd URL (hopefully)
 */
function pressit_validate($value) {
	if(empty($value))
		return $value;

	if($value[strlen($value)-1] != '/') {
		$value .= '/';
	}
	return $value;
}

add_action('register_options', 'pressit_options');
?>