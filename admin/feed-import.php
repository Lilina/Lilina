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
		MessageHandler::add_error(_r('No URL specified to import OPML from'));
	else
		$importing = import_opml($_REQUEST['url']);
}

admin_header(_r('Import Feeds'), 'feeds.php');
?>
<h1><?php _e('Import Feeds'); ?></h1>
<?php
if(!$importing) {
?>
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
}
else {
?>
<p><?php _e('Currently importing feeds. Please keep this page open in your browser until all feeds have been processed.'); ?></p>
<ul id="log">
</ul>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>admin/importer.js"></script>
<script type="text/javascript">
var feeds_to_add = <?php
	echo json_encode($importing);
?>;
$(document).ready(function (){
	importer.init(feeds_to_add);
	importer.end_callback = function () {
		$("#main").append("<p><?php _e('Finished importing feeds.') ?></p>");
	};
});
</script>
<?php
}
admin_footer();
?>