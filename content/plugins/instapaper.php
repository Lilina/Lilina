<?php
/**
 * Instapaper
 *
 * Save items to Instapaper.
 *
 * @id glo:instapaper
 * @version 1.1
 * @author Ryan McCue <ryan@getlilina.org>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class InstapaperService extends Lilina_Service_Local {
	public function __construct() {
		$this->name = 'Instapaper';
		$this->description = 'Use the Instapaper service';
		$this->label = 'Read Later';
		$this->method = 'instapaper';
		$this->nonce = 'instapaper-submit';
		$this->icon = 'http://www.instapaper.com/images/press-kit/icon-16.png';
		parent::__construct();
	}
}

class Instapaper {
	public static function register() {
		// Register the method
		Controller::registerMethod('instapaper', array('Instapaper', 'page'));

		// Register the Instapaper service
		$service = new InstapaperService();
		Services::register('instapaper', $service);

		// Register our options
		add_action('register_options', array('Instapaper', 'options'));
		add_action('options-form', array('Instapaper', 'admin'));		
	}

	/**
	 * instapaper method
	 */
	public static function page() {
		$user = new User();
		if(!$user->identify()) {
			Instapaper::error(sprintf(_r('Please <a href="%s">log in</a> first', 'instapaper'), get_option('baseurl') . 'admin/login.php'));
		}

		if(get_option('instapaper_user') === null)
			Instapaper::error(sprintf(_r('Please set your username in the <a href="%s">settings</a>.', 'instapaper'), get_option('baseurl') . 'admin/settings.php'));


		if(empty($_GET['item'])) {
			Instapaper::error(_r('No item ID specified.', 'instapaper'));
		}

		try {
			Instapaper::submit();
		}
		catch (Exception $e) {
			Instapaper::error($e->getMessage());
		}
	}

	protected static function submit() {
		$id = $_GET['item'];
		$item = Items::get_instance()->get_item($id);

		if ( false === $item ) {
			throw new Exception(_r('Invalid item ID specified', 'instapaper'));
		}

		$user = get_option('instapaper_user');
		if ( empty( $user ) )
			throw new Exception(sprintf(_r('Please set your username and password in the <a href="%s">settings</a>.', 'instapaper'), get_option('baseurl') . 'admin/settings.php'));

		if(!check_nonce('instapaper-submit', $_GET['_nonce']))
			throw new Exception(_r('Nonces did not match. Try again.', 'instapaper'));

		$data = array(
			'username' => get_option('instapaper_user', ''),
			'password' => get_option('instapaper_pass', ''),
			'url' => $item->permalink,
			'title' => apply_filters( 'the_title', $item->title ),
			//'selection' => $_GET['selection'] // Support for this coming later.
		);

		$response = Lilina_HTTP::post("https://www.instapaper.com/api/add", array(), $data, array('redirects' => 2));

		switch ( $response->status_code ) {
			case 400:
				throw new Exception(_r('Internal error. Please report this.', 'instapaper'));
			case 403:
				throw new Exception(sprintf(_r('Invalid username/password. Please check your details in the <a href="%s">settings</a>.', 'instapaper'), get_option('baseurl') . 'admin/settings.php'));
			case 500:
				throw new Exception(_r('An error occurred when contacting Instapaper. Please try again later.', 'instapaper'));
		}
		Instapaper::page_head();
	?>
		<div id="message">
			<h1><?php _e('Success!'); ?></h1>
			<p class="sidenote"><?php _e('Closing window in...', 'instapaper') ?></p>
			<p class="sidenote" id="counter">3</p>
		</div>
		<script>
			$(document).ready(function () {
				setInterval(countdown, 1000);
			});

			function countdown() {
				if(timer > 0) {
					$('#counter').text(timer);
					timer--;
				}
				else {
					self.close();
				}
			}

			var timer = 2;
		</script>
	<?php
		Instapaper::page_foot();
		die();
	}


	protected static function page_head() {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title><?php _e('Instapaper', 'instapaper') ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/core.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/mini.css" media="screen"/>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.js"></script>
	</head>
	<body id="admin-subscribe" class="admin-page">
	<?php
	}

	protected static function page_foot() {
	?>
		<script type="text/javascript">
			$(document).ready(function () {
				$(".optional").hide();
				$("<p class='hideshow'><span><?php _e('Show advanced options', 'instapaper') ?></span></p>").insertBefore(".optional").click(function () {
					$(this).siblings(".optional").show();
					$(this).hide();
				});
			});
		</script>
	</body>
	</html>
	<?php
	}

	protected static function error($msg) {
		Instapaper::page_head();
	?>
		<div id="main">
			<h1><?php _e('Whoops!', 'instapaper') ?></h1>
			<div id="error">
				<p><?php echo $msg; ?></p>
			</div>
		</div>
	<?php
		Instapaper::page_foot();
		die();
	}

	/**
	 * Register our option and associated admin page
	 */
	public static function options() {
		register_option('instapaper_user');
		register_option('instapaper_pass');
	}

	public static function admin() {
	?>
		<fieldset id="instapaper">
			<legend><?php _e('Instapaper Settings', 'instapaper'); ?></legend>

			<div class="row">
				<label for="instapaper_user"><?php _e('Username / Email Address', 'instapaper') ?></label>
				<input type="text" name="instapaper_user"
					id="instapaper_user" value="<?php echo get_option('instapaper_user', ''); ?>" />
			</div>

			<div class="row">
				<label for="instapaper_pass"><?php _e('Password (optional)', 'instapaper') ?></label>
				<input type="password" name="instapaper_pass"
					id="instapaper_pass" value="<?php echo get_option('instapaper_pass', ''); ?>" />
				<p class="sidenote"><?php _e("If you didn't set a password when signing up to Instapaper, you don't have one.", 'instapaper') ?></p>
			</div>
		</fieldset>
	<?php
	}
}

Instapaper::register();