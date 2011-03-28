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

class PressItService implements Lilina_Service {
	public function __construct() {
		$wp_url = get_option('pressit_wpurl', '');
		$action = $wp_url . 'wp-admin/press-this.php?u={permalink}&t={title}&s={summary}';
		$this->options = array(
			'name' => _r('Press It', 'pressit'),
			'description' => _r('Post an item to your WordPress blog', 'pressit'),
			'label' => _r('Press It', 'pressit'),
			'type' => 'external',
			'action' => $action
		);
	}
	public function action() {
		return $this->options['action'];
	}
	public function set_action($action) {
		$this->options['action'] = $action;
	}
	public function export() {
		return $this->options;
	}
}

function pressit_register() {
	if (get_option('pressit_wpurl', '') === '') {
		return;
	}
	$pressit = new PressItService();
	Services::register('pressit', $pressit);
}
add_action('init', 'pressit_register');

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