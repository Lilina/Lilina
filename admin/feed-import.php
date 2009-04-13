<?php
/**
 * Feeds page
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

$lilina_importers = array();

/** */
require_once('admin.php');

$action = (isset($_REQUEST['action'])? $_REQUEST['action'] : '');
$service = (isset($_REQUEST['service']) ? $_REQUEST['service'] : 'other');

/**
 * Register an importer for use
 *
 * @param string $uid Importer tag. Used to uniquely identify importer.
 * @param string $name Importer name and title.
 * @param string $description Importer description.
 * @param callback $callback Callback to run.
 */
function register_importer($uid, $name, $description, $callback) {
	global $lilina_importers;
	$lilina_importers[$uid] = array($name, $description, $callback);
}

/**
 * Standard importing interface, to be called by an importer
 *
 * @param array $feeds Associative array containing 'url', 'title' and 'cat'
 */
function import($feeds) {
	?>
<p><?php _e('Currently importing feeds. Please keep this page open in your browser until all feeds have been processed.'); ?></p>
<p class="sidenote"><?php _e('Please note: Javascript must be enabled to import feeds.'); ?></p>
<ul id="log">
</ul>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>admin/importer.js"></script>
<script type="text/javascript">
var feeds_to_add = <?php echo json_encode($feeds); ?>;
$(document).ready(function (){
	importer.init(feeds_to_add);
	importer.end_callback = function () {
		$("#main").append("<p><?php _e('Finished importing feeds.') ?></p>");
	};
});
</script>
<?php
}

/** Load all the importers, so that they may register themselves */
foreach(glob(LILINA_PATH . '/admin/includes/import/*.php') as $file) {
	require_once($file);
}

$lilina_importers = apply_filters('importer', $lilina_importers);

/** Run the importer */
if(!empty($service) && isset($lilina_importers[$service])) {
	call_user_func($lilina_importers[$service][2]);
	die();
}

admin_header(_r('Import Feeds'), 'feeds.php');
?>
<h1><?php _e('Importer'); ?></h1>
<?php
if(empty($lilina_importers)) {
?>
<p><?php _e('No importers available on your system. Weird.') ?></p>
<?php
}
else {
?>
<p><?php _e('The following importers are available:') ?></p>
<table class="item-table">
	<thead>
		<tr>
			<th scope="col"><?php _e('Name') ?></th>
			<th scope="col"><?php _e('Description') ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$count = 0;
foreach($lilina_importers as $id => $importer) {
?>
		<tr id="importer-<?php echo $id ?>" class="<?php echo ($count % 2) ? 'alt' : ''; ?>">
			<td><a href="feed-import.php?service=<?php echo $id ?>"><?php echo $importer[0] ?></a></td>
			<td><?php echo $importer[1] ?></td>
		</tr>
<?php
	$count++;
}
?>
	</tbody>
</table>
<?php
}
admin_footer();
?>