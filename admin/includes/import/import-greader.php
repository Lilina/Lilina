<?php
/**
 * Google Reader-to-Lilina importer
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
require_once(LILINA_PATH . '/admin/includes/import/import-other.php');
require_once(LILINA_INCPATH . '/contrib/googlereaderapi.php');

/**
 * Google Reader-to-Lilina importer
 *
 * @package Lilina
*/
class GoogleReader_Import extends OPML_Import {
	public function __construct($name) {
		parent::__construct($name);
	}
	public function dispatch() {
		$step = ( !empty($_POST['step']) ? $_POST['step'] : 0 );
		switch($step) {
			case 0:
				$this->introduction();
				break;
			case 1:
				$this->import();
				break;
		}
	}
	public function introduction() {
		admin_header($this->name);
?>
<h1><?php echo $this->name ?></h1>
<p><?php _e('There are several ways to import from Google Reader.'); ?></p>
<h2><?php _e('Method 1'); ?></h2>
<p><?php printf(
	_r('<a href="%1$s">Export</a> your feeds from Google reader and then use the <a href="%2$s">OPML importer</a>.'),
	'http://www.google.com/reader/subscriptions/export',
	'feed-import.php?service=opml'
	); ?></p>
<h2><?php _e('Method 2'); ?></h2>
<p><?php _e("We can grab your OPML file for you, but we'll need your username and password. This information won't be stored anywhere and is only used once. (It sucks, we know, but Google doesn't offer any other way.)"); ?>
<form action="feed-import.php" method="POST">
	<fieldset id="greader">
		<div class="row">
			<label for="user"><?php _e('Username (Email address)'); ?>:</label>
			<input type="text" name="user" id="user" />
		</div>
		<div class="row">
			<label for="pass"><?php _e('Password'); ?>:</label>
			<input type="password" name="pass" id="pass" />
		</div>
		<input type="submit" value="<?php _e('Import'); ?>" class="button positive" name="submit" />
		<input type="hidden" name="step" value="1" />
		<input type="hidden" name="service" value="greader" />
	</fieldset>
</form>
<?php
		admin_footer();
	}
	protected function import() {
		admin_header($this->name);
		try {
			// I'm not in favour of allowing user input to pass through
			// unsanitized, but it's URL encoded in the request library, so
			// we'll let Google handle it.
			$this->api = new GoogleReaderAPI($_POST['user'], $_POST['pass']);
			$this->api->connect();
			$opml = $this->api->call();
			$feeds = $this->import_opml($opml);
			import($feeds);
		}
		catch (Exception $e) {
			$this->error($e);
		}
		admin_footer();
	}
}

$greader_importer = new GoogleReader_Import(_r('Google Reader Importer'));
register_importer('greader', _r('Google Reader'), _r('Import feeds from Google Reader'), array(&$greader_importer, 'dispatch'));