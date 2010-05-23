<?php
/*
Plugin Name: Instapaper
Description: Save items to Instapaper.
*/

// Front-End
/**
 * Add the link to the item actions
 *
 * @param array $actions Previous actions
 * @return array Array with Tweet It added.
 */
function instapaper_button($actions) {
	require_once(LILINA_PATH . '/admin/includes/common.php');
	$tweet_url = sprintf(get_option('baseurl') . '?method=instapaper&id=%1$s&_nonce=%2$s', get_the_id(), generate_nonce());

	$actions[] = '<a href="' . $tweet_url . '" class="instapaper_button" title="' . _r('Save to Instapaper', 'instapaper') . '">' . _r('Read Later', 'instapaper') . '</a>';

	return $actions;
}
add_filter('action_bar', 'instapaper_button');

/**
 * Javascript to open URL in new window
 */
function instapaper_js() {
?>
<script type="text/javascript">
	$(document).ready(function() {
		$(".instapaper_button").live("click", function () {
			var result = window.open($(this).attr('href'),'t','toolbar=0,resizable=0,scrollbars=1,status=1,width=450,height=430');
			if(!result)
				return true;
			return false;
		});
	});
</script>
<?php
}
add_action('template_header', 'instapaper_js');

/**
 * instapaper method
 */
function instapaper_page() {
	require_once(LILINA_PATH . '/admin/includes/common.php');

	$user = new User();
	if(!$user->identify()) {
		instapaper_error(sprintf(_r('Please <a href="%s">log in</a> first', 'instapaper'), get_option('baseurl') . 'admin/login.php'));
	}

	if(get_option('instapaper_user') === null)
		instapaper_error(sprintf(_r('Please set your username in the <a href="%s">settings</a>.', 'instapaper'), get_option('baseurl') . 'admin/settings.php'));


	if(empty($_GET['id'])) {
		instapaper_error(_r('No item ID specified.', 'instapaper'));
	}

	try {
		instapaper_submit();
	}
	catch (Exception $e) {
		instapaper_error($e->getMessage());
	}
}

/**
 * Register the tweetit method
 */
function instapaper_register(&$controller) {
	$controller->registerMethod('instapaper', 'instapaper_page');
}
add_action('controller-register', 'instapaper_register', 10, 1);

function instapaper_submit() {
	$id = $_GET['id'];
	$item = Items::get_instance()->get_item($id);

	if ( false === $item ) {
		throw new Exception(_r('Invalid item ID specified', 'instapaper'));
	}

	$user = get_option('instapaper_user');
	if ( empty( $user ) )
		throw new Exception(sprintf(_r('Please set your username and password in the <a href="%s">settings</a>.', 'instapaper'), get_option('baseurl') . 'admin/settings.php'));

	if(!check_nonce($_GET['_nonce']))
		throw new Exception(_r('Nonces did not match. Try again.', 'instapaper'));

	$data = array(
		'username' => get_option('instapaper_user', ''),
		'password' => get_option('instapaper_pass', ''),
		'url' => $item->permalink,
		'title' => apply_filters( 'the_title', $item->title ),
		//'selection' => $_GET['selection'] // Support for this coming later.
	);

	$request = new HTTPRequest('', 2);
	$response = $request->post("https://www.instapaper.com/api/add", array(), $data);

	switch ( $response->status_code ) {
		case 400:
			throw new Exception(_r('Internal error. Please report this.', 'instapaper'));
		case 403:
			throw new Exception(sprintf(_r('Invalid username/password. Please check your details in the <a href="%s">settings</a>.', 'instapaper'), get_option('baseurl') . 'admin/settings.php'));
		case 500:
			throw new Exception(_r('An error occurred when contacting Instapaper. Please try again later.', 'instapaper'));
	}
	instapaper_page_head();
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
	instapaper_page_foot();
	die();
}


function instapaper_page_head() {
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

function instapaper_page_foot() {
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

function instapaper_error($msg) {
	instapaper_page_head();
?>
	<div id="main">
		<h1><?php _e('Whoops!', 'instapaper') ?></h1>
		<div id="error">
			<p><?php echo $msg; ?></p>
		</div>
	</div>
<?php
	instapaper_page_foot();
	die();
}

/**
 * Register our option and associated admin page
 */
function instapaper_options() {
	register_option('instapaper_user');
	register_option('instapaper_pass');
}
add_action('register_options', 'instapaper_options');

function instapaper_admin() {
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
add_action('options-form', 'instapaper_admin');