<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		install-finish.php
Purpose:	Just finish off the install
Notes:		Must turn off the non-fatal ones
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
?>
<h1>Step 1 - Lilina Installation</h1>
<p>Let's get started on the installation!</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<div class="form_row">
		<label for="url">URL for Lilina (example: <span style="color: #1BECAC;">http://cubegames.net/</span> )</label>
		<input type="text" value="http://cubegames.net/" name="url" id="url" />
	</div>
	<div style="clear:both;">&nbsp;</div>
</form>