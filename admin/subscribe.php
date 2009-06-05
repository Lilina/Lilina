<?php
/**
 * @todo Move to admin/login.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require_once('admin.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Subscribe &mdash; <?php echo get_option('sitename'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/core.css" media="screen"/>
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/mini.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>admin/admin.js"></script>
</head>
<body id="admin-subscribe" class="admin-page">
	<div id="header">
		<p id="sitetitle"><a href="<?php echo get_option('baseurl'); ?>"><?php echo get_option('sitename'); ?></a></p>
		<p id="homelink"><a href="index.php">Home</a></p>
	</div>
	<div id="main">
		<form action="feeds.php" method="get" id="add_form">
			<fieldset id="add">
				<h2><?php _e('Add Feed'); ?></h2>
				<div class="row">
					<label for="add_name"><?php _e('Name'); ?>:</label>
					<input type="text" name="add_name" id="add_name" />
					<p class="sidenote"><?php _e('If no name is specified, it will be taken from the feed'); ?></p>
				</div>
				<div class="row">
					<label for="add_url"><?php _e('Feed address (URL)'); ?>:</label>
					<input type="text" name="add_url" id="add_url" />
					<p class="sidenote"><?php _e('Example'); ?>: http://feeds.feedburner.com/lilina-news, http://getlilina.org</p>
				</div>
				<input type="hidden" name="action" value="add" />
				<input type="submit" value="<?php _e('Add'); ?>" class="submit" />
				<p class="loading"><?php _e('Adding feed&hellip;') ?></p>
			</fieldset>
		</form>
	</div>
</body>
</html>