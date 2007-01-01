<?php
$error = 0;
//Register with main server for stats
if(!$settings['localhost']) {
	if(!ping('http://lilina.sourceforge.net/ping.php', $settings['baseurl'], 'New Site', 'New Lilina site set up')) {
		$error = 'Unable to register with remote server';
	}
}

if(!$error) {
?>
<h1>Congratulations!</h1>
<p>Lilina has been set up on your server and is ready to run. Open <a href="<?php echo $settings['baseurl']; ?>admin.php">your admin panel</a> and add some feeds.<br />
Or, you could just open up a bottle of champagne!</p>
<?php
}
else {
?>
<h1>Error!</h1>
<p>Unfortunately, there has been an error. The error returned was:<br />
<?php echo $error; ?><br />
Please try again by refreshing. If you would like more information about this error, visit the Knowledge Base.<br />
Sorry for the inconvenince.</p>
<?php
}
?>