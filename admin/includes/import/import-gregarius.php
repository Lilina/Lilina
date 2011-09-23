<?php
/**
 * Gregarius-to-Lilina importer
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @subpackage Importers
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
require_once(LILINA_PATH . '/admin/includes/import/import-other.php');

/**
 * Gregarius-to-Lilina importer
 *
 * @package Lilina
 * @subpackage Importers
 */
class Gregarius_Import extends OPML_Import {
	protected $name;
	protected $old;

	public function __construct($name) {
		$this->name = $name;
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
			case 2:
				$this->import_items();
		}
	}
	protected function import_feeds($feeds) {
		foreach ($feeds as $feed) {
			try {
				$result = Feeds::get_instance()->add($feed->url, $feed->title);
				echo '<li>' . $result['msg'] . '</li>';
				$this->old[$feed->id] = $result['id'];
			}
			catch (Exception $e) {
				$id = sha1($feed->url);
				$this->old[$feed->id] = $id;
			}
		}
		return $feeds;
	}
	protected function import_item($old) {
		$new = $this->convert_item($old);
		return Items::get_instance()->check_item($new);
	}
	protected function convert_item($old) {
		$new = (object) array(
			'hash' => sha1($old->guid),
			'timestamp' => strtotime($old->pubdate),
			'title' => $old->title,
			'content' => $old->description,
			'summary' => $old->description,
			'permalink' => $old->url,
			'metadata' => (object) array(
				'enclosure' => $old->enclosure,
				'enclosure_data' => false
			),
			'author' => array(
				'name' => $old->author,
				'url' => false
			),
			'feed' => false,
			'feed_id' => $this->lookup_cid($old->cid)
		);
		return $new;
	}
	protected function lookup_cid($cid) {
		if (!empty($this->old[$cid]))
			return $this->old[$cid];
		throw new Exception('Feed does not exist');
	}
	public function introduction() {
		admin_header($this->name);
?>
<h1><?php echo $this->name ?></h1>
<p><?php _e('Lilina can import your feeds and items from Gregarius.'); ?></p>
<p><?php printf(
	_r("To import the items, we first need your database details. If you'd prefer not to, you can use the <a href='%s'>OPML importer</a> instead."),
	'feed-import.php?service=opml'
	)
?></p>
<form action="feed-import.php" method="POST">
	<legend><?php _e('Database details'); ?></legend>
	<div class="row">
		<label for="user"><?php _e('Database username'); ?>:</label>
		<input type="text" name="user" id="user" />
	</div>
	<div class="row">
		<label for="pass"><?php _e('Database password'); ?>:</label>
		<input type="password" name="pass" id="pass" />
	</div>
	<div class="row">
		<label for="dsn"><?php _e('DSN'); ?>:</label>
		<input type="text" name="dsn" id="dsn" value="mysql:dbname=gregarius;host=127.0.0.1" />
		<p class="sidenote"><?php _e("If you don't know how to format a DSN, just replace 'gregarius' with your database name.") ?></p>
	</div>
	<input type="submit" value="<?php _e('Import'); ?>" class="button positive" name="submit" />
	<input type="hidden" name="step" value="1" />
	<input type="hidden" name="service" value="gregarius" />
</form>
<?php
		admin_footer();
	}
	protected function import() {
		admin_header($this->name);
		try {
			$this->db = array(
				'user' => $_POST['user'],
				'pass' => $_POST['pass'],
				'dsn' => $_POST['dsn'],
			);
			
			$this->db = new PDO($_POST['dsn'], $_POST['user'], $_POST['pass']);
			$feeds = $this->db->prepare("SELECT * FROM channels");
			$feeds->execute();
			echo '<p>' . _r('Importing feeds&hellip;') . '</p><ul>';
			$this->import_feeds($feeds->fetchAll(PDO::FETCH_OBJ));
			echo '</ul>';

			$this->import_items();
		}
		catch (Exception $e) {
			$this->error($e);
		}
		admin_footer();
	}
	protected function import_items() {
		try {
			$items = $this->db->prepare("SELECT * FROM item");
			$items->execute();
			$items = $items->fetchAll(PDO::FETCH_OBJ);

			echo '<p>' . _r('Importing items&hellip;') . '</p>';
			$count = 0;
			foreach ($items as $item) {
				if ($this->import_item($item)) {
					$count++;
				}
			}
			Items::get_instance()->save_cache();
			printf('<p>' . _r('Done! Imported %d items.') . '</p>', $count);
		}
		catch (Exception $e) {
			$this->error($e);
		}
	}

	protected function error($e) {
	?>
<h1><?php echo $this->name ?></h1>
<p><?php echo $e->getMessage(); ?></p>
<p><?php printf(_r('To retry, go <a href="%s">back</a>.'), 'feed-import.php?service=gregarius'); ?></p>
<?php
	}
}

$gregarius_importer = new Gregarius_Import(_r('Gregarius Importer'));
register_importer('gregarius', _r('Gregarius'), _r('Import feeds from a local installation of Gregarius'), array(&$gregarius_importer, 'dispatch'));