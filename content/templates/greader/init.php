<?php

class Raincoat {
	public static function register() {
		Controller::registerMethod('raincoat_help', array('Raincoat', 'help'));
	}
	public static function help() {
?><!DOCTYPE html>
<html>
	<head>
		<title>Raincoat Help</title>
		<link rel="stylesheet" type="text/css" href="<?php template_directory(); ?>/reset.css" />
		<style>
			body {
				font-family: Lucida Grande, Helvetica, Arial, sans-serif;
				font-size: 14px;
				text-align: center;
			}
			h1 {
				font-size: 24px;
			}
			dt {
				font-weight: bold;
				margin: 1em 0 0.7em;
			}
			dd {
				padding: 0;
				margin: 0;
			}
		</style>
	</head>
	<body>
		<h1>Keyboard Shortcuts</h1>
		<dl>
			<dt>j/k</dt>
			<dd>next/previous item</dd>
			<dt>n/p</dt>
			<dd>select next/previous item (don't open)</dd
			<dt>o</dt>
			<dd>show/hide item</dd>
			<dt>v</dt>
			<dd>view original article</dt>
			<dt>?</dt>
			<dd>show help</dd>
			<dt>esc</dt>
			<dd>close help</dd>
		</dl>
	</body>
</html>
<?php
	}
}

Raincoat::register();