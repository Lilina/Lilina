<?php
/**
 * @todo Move to admin/login.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Administration - Login</title>
</head>
<body>
<div id="login" style="border:1px solid #777; background: #ddd; margin-top:1em;padding:1em;padding-bottom:0em;">
<?php
if(isset($error) && $error == 'error') {
?>	<p style="font-weight:bold; color:#E60000;"><?php _e('Your password or username is incorrect. Please make sure you have spelt it correctly.') ?></p>
<?php
}
?>	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<label for="user"><?php _e('Username'); ?>:</label>
		<input type="text" name="user" id="user" />
		<label for="pass"><?php _e('Password'); ?>:</label>
		<input type="password" name="pass" id="pass" />
		<input type="submit" value="<?php _e('Login'); ?>" />
	</form>
</div>
</body>
</html>