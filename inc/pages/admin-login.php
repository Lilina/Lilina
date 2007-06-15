<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		admin-login.php
Purpose:	Just finish off the install
Notes:		Must turn off the non-fatal ones
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Administration - Login</title>
</head>
<body>
<div id="login">
<?php
if(isset($error_message) && !empty($error_message)) {
	echo '<p style="font-weight:bold; color:#E60000;">' . $error_message . '</p>';
}
echo $content;
?>
</div>
</body>
</html>