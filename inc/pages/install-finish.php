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
global $retrieved_settings;
if(lilina_set_settings($retrieved_settings)){
?>
<h1>Congratulations!</h1>
<p>Lilina has been set up on your server and is ready to run. Open <a href="admin.php">your admin panel</a> and add some feeds.<br />
Or, you could just open up a bottle of champagne!</p>
<?php
}
?>