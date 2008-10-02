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

/**
 * 
 * @global array
 */
function get_feed_list() {
	global $data;
	if(isset($data['feeds']))
		return $data['feeds'];
	return false;
}

/**
 * feed_list_table() - {@internal Missing Short Description}}
 *
 * {@internal Missing Long Description}}
 */
function feed_list_table() {
	//Defined in admin panel
	$feeds			= get_feed_list();
	$j	= 0;
	if(is_array($feeds) && !empty($feeds)) {
		foreach($feeds as $this_feed) {
	?>
		<tr class="<?php echo $alt; ?>">
			<td class="id-col"><?php echo $j; ?></td>
			<td class="name-col"><?php echo stripslashes($this_feed['name']); ?></td>
			<td class="url-col"><?php echo $this_feed['feed']; ?></td>
			<td class="cat-col"><?php echo $this_feed['cat']; ?></td>
			<?php do_action('admin-feeds-infocol', $this_feed, $j); ?>
			<td class="change-col"><a href="<?php echo  $_SERVER['PHP_SELF']; ?>?page=feeds&amp;change=<?php echo  $j; ?>&amp;action=change" class="change_link"><?php _e('Change'); ?></a></td>
			<td class="remove-col"><a href="<?php echo  $_SERVER['PHP_SELF']; ?>?page=feeds&amp;remove=<?php echo  $j; ?>&amp;action=remove"><?php _e('Remove'); ?></a></td>
			<?php do_action('admin-feeds-actioncol', $this_feed, $j); ?>
		</tr>
	<?php
			$alt = empty($alt) ? 'alt' : '';
			++$j;
		}
	}
	else {
	?>
		<tr id="nofeeds"><td><?php _e('No feeds installed yet'); ?></td></tr>
	<?php
	}
}

$change_name	= (isset($_REQUEST['change_name']))? $_REQUEST['change_name'] : '';
$change_name	= htmlspecialchars($change_name);
$change_url	= (isset($_REQUEST['change_url']))? $_REQUEST['change_url'] : '';
$change_id	= (isset($_REQUEST['change_id']))? $_REQUEST['change_id'] : '';
$change_id	= htmlspecialchars($change_id);

//Remove variables
$remove_id	= (isset($_REQUEST['remove']))? $_REQUEST['remove'] : '';
$remove_id	= htmlspecialchars($remove_id);

//Import variable
$action = (isset($_REQUEST['action'])? $_REQUEST['action'] : '');
$importing = false;

/** Make sure we're actually adding */
switch($action) {
	case 'add':
	/** We need some sort of value here */
	if( !isset($_REQUEST['add_name']) )
		$_REQUEST['add_name'] = '';

	if(!isset($_REQUEST['add_url']))
		add_notice(_r('No URL specified'));
	else
		add_feed($_REQUEST['add_url'], $_REQUEST['add_name']);
	break;

	case 'import':
		if(!isset($_REQUEST['import_url']))
			add_notice(_r('No URL specified to import OPML from'));
		else
			$importing = import_opml($_REQUEST['import_url']);
		break;

	case 'remove':
		$removed = $data['feeds'][$remove_id];
		unset($data['feeds'][$remove_id]);
		$data['feeds'] = array_values($data['feeds']);
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen(get_option('files', 'feeds'),'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		add_notice(sprintf(_r('Removed feed &mdash; <a href="%s">Undo</a>?'), htmlspecialchars($_SERVER['PHP_SELF']) . '?page=feeds&amp;action=add&amp;add_name=' . urlencode($removed['name']) . '&amp;add_url=' . urlencode($removed['feed'])));
		break;

	case 'change':
		$data['feeds'][$change_id]['feed'] = $change_url;
		if(!empty($change_name)) {
			$data['feeds'][$change_id]['name'] = $change_name;
		}
		else {
			//Need to have a similar function to add_feed()
		}
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen(get_option('files', 'feeds'),'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		add_notice(sprintf(_r('Changed "%s" (#%d)'), $change_name, $change_id));
	break;
}
if(isset($_REQUEST['ajax']) && !isset($_REQUEST['list'])) {
	save_feeds();
	echo json_encode(array('errors' => MessageHandler::get_errors(), 'messages' => MessageHandler::get_messages()));
	die();
}
elseif(isset($_REQUEST['list']) && isset($_REQUEST['ajax'])) {
	die( feed_list_table() );
}


admin_header(_r('Feeds'));
?>
<h1><?php _e('Feeds'); ?></h1>
<h2><?php _e('Current Feeds'); ?></h2>
<table id="feeds_list" class="item-table">
	<thead>
		<tr>
		<th>#</th>
		<th><?php _e('Feed Name'); ?></th>
		<th><?php _e('URL'); ?></th>
		<th><?php _e('Category'); ?></th>
		<?php do_action('admin-feeds-infocol-description', $this_feed, $j); ?>
		<th class="change-col"><?php _e('Change Feed'); ?></th>
		<th class="remove-col"><?php _e('Remove Feed'); ?></th>
		<?php do_action('admin-feeds-actioncol-description', $this_feed, $j); ?>
		</tr>
	</thead>
	<tbody>
<?php
	feed_list_table();
?>
	</tbody>
</table>
<div id="changer">
	<form action="feeds.php" method="get" id="change_form">
		<fieldset id="change">
			<legend><?php _e('Edit Feed'); ?></legend>
			<div class="row">
				<label for="change_name"><?php _e('Name'); ?>:</label>
				<input type="text" name="change_name" id="change_name" />
			</div>
			<div class="row">
				<label for="change_url"><?php _e('Feed address (URL)'); ?>:</label>
				<input type="text" name="change_url" id="change_url" />
				<p class="sidenote"><?php _e('Example'); ?>: http://feeds.feedburner.com/lilina-news, http://getlilina.org</p>
			</div>
			<div class="row">
				<label for="change_cat"><?php _e('Category'); ?>:</label>
				<select name="change_cat" id="change_cat">
				<?php
				foreach(get_categories() as $category) {
					echo "<option value='{$category['id']}'>{$category['name']}</option>";
				}
				?>
				</select>
			</div>
			<input type="hidden" name="action" value="change" />
			<div id="changer_id" class="row">
				<label for="change_id"><?php _e('Feed ID'); ?>:</label>
				<input type="text" name="change_id" id="change_id" value="" />
			</div>
			<input type="submit" value="<?php _e('Save'); ?>" class="submit" />
		</fieldset>
	</form>
</div>
<form action="feeds.php" method="get" id="add_form">
	<fieldset id="add">
		<legend><?php _e('Add Feed'); ?></legend>
		<div class="row">
			<label for="add_name"><?php _e('Name'); ?>:</label>
			<input type="text" name="add_name" id="add_name" />
			<p class="sidenote"><?php _e('If no name is specified, it will be taken from the feed'); ?></p>
		</div>
		<div class="row">
			<label for="add_url"><?php _e('Feed address (URL)'); ?>:</label>
			<input type="text" name="add_url" id="add_url" />
			<p class="sidenote"><?php _e('Example'); ?>: http://feeds.feedburner.com/lilina-news, http://getlilina.org</p>
		</div>
		<input type="hidden" name="action" value="add" />
		<input type="submit" value="<?php _e('Add'); ?>" class="submit" />
	</fieldset>
</form>
<form action="feeds.php" method="get" id="import_form">
	<fieldset id="import">
		<legend><?php _e('Import Feeds'); ?></legend>
		<div class="row">
			<label for="import_url"><?php _e('OPML address (URL)'); ?>:</label>
			<input type="text" name="import_url" id="import_url" />
		</div>
		<input type="hidden" name="action" value="import" />
		<input type="submit" value="<?php _e('Import'); ?>" class="submit" />
	</fieldset>
</form>
<?php
if($importing) {
?>
<script type="text/javascript">
var feeds_to_add = <?php
	echo json_encode($importing);
?>;
</script>
<?php
}
admin_footer();
?>