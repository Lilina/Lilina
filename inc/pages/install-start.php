<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		install-start.php
Purpose:	Get settings
Notes:		Must turn off the non-fatal ones
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
?>
<h1>Step 1 - Lilina Installation</h1>
<p>Let's get started on the installation!</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="width: 350px; background-color: #CCCCCC; border: 1px dotted #333; padding: 5px; margin: 0px auto;">
	<h2>General Settings</h2>
	<div class="form_row">
		<label for="sitename">
			<span class="label">Name of site</span>
		</label>
		<span class="formw">
			<input type="text" value="Lilina News Aggregator" name="sitename" id="sitename" size="40" />
		</span>
	</div>
	<div class="form_row">
		<label for="url">
			<span class="label">URL for Lilina</span>
		</label>
		<span class="formw">
			<input type="text" value="http://localhost/" name="url" id="url" size="40" />
		</span>
	</div>
	<h2>Security Settings</h2>
	<div class="form_row">
		<label for="username">
			<span class="label">Admin Username</span>
		</label>
		<span class="formw">
			<input type="text" value="username" name="username" id="username" size="40" />
		</span>
	</div>
	<div class="form_row">
		<label for="password">
			<span class="label">Admin Password</span>
		</label>
		<span class="formw">
			<input type="text" value="password" name="password" id="password" size="40" />
		</span>
	</div>	
	<div class="form_row">
		<span class="formw">
			<input type="hidden" value="1" name="from" id="from" />
			<input type="submit" value="Next &gt;" />
		</span>
	</div>
	<div style="clear:both;">&nbsp;</div>
</form>