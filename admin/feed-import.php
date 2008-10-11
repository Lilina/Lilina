<?php
/**
 * Feeds page
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
require_once('admin.php');

require_once(LILINA_INCPATH . '/core/category-functions.php');

//Import variable
$action = (isset($_REQUEST['action'])? $_REQUEST['action'] : '');
$importing = false;

/** Make sure we're actually adding */
if(isset($_REQUEST['submit'])) {
	if(!isset($_REQUEST['url']))
		add_notice(_r('No URL specified to import OPML from'));
	else
		$importing = import_opml($_REQUEST['url']);
}

admin_header(_r('Import Feeds'), 'feeds.php');
?>
<h1><?php _e('Import Feeds'); ?></h1>
<form action="feed-import.php" method="get" id="import_form">
	<fieldset id="import">
		<legend><?php _e('Import Feeds'); ?></legend>
		<div class="row">
			<label for="url"><?php _e('OPML address (URL)'); ?>:</label>
			<input type="text" name="url" id="url" />
		</div>
		<input type="submit" value="<?php _e('Import'); ?>" class="submit" name="submit" />
	</fieldset>
</form>
<?php
if($importing) {
?>
<script type="text/javascript">
var feeds_to_add = <?php
	echo json_encode($importing);
?>;
$(document).ready(function (){
	feeds.process(feeds_to_add);
});
</script>
<?php
}
admin_footer();
?>