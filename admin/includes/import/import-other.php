<?php
/**
 * OPML-to-Lilina importer
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
 * OPML-to-Lilina importer
 *
 * @package Lilina
*/
class OPML_Import {
	protected $name = '';
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
		}
	}
	/**
	 * Parse feeds into an array, ready to pass to the Javascript importer
	 *
	 * @param string $opml OPML standard file.
	 * @return array Associative array containing feed URL, title and category (if applicable)
	 */
	protected function import_opml($opml) {
		if(empty($opml)) {
			throw new Exception(_r('No OPML specified'));
			return false;
		}
		$opml = new OPMLParser($opml);

		if(!empty($opml->error) || empty($opml->data)) {
			throw new Exception(sprintf(_r('The OPML file could not be read. The parser said: %s'), $opml->error));
			return false;
		}
		$feeds_num = 0;
		foreach($opml->data as $cat => $feed) {
			if(!isset($feed['xmlurl']) && isset($feed[0]['xmlurl'])) {
				foreach($feed as $subfeed) {
					$feeds[] = array('url' => $subfeed['xmlurl'], 'title' => $subfeed['title'], 'cat' => $cat);
					++$feeds_num;
				}
				continue;
			}

			$feeds[] = array('url' => $feed['xmlurl'], 'title' => $feed['title'], 'cat' => '');
			++$feeds_num;
		}
		MessageHandler::add(sprintf(Locale::ngettext('Adding %d feed', 'Adding %d feeds', $feeds_num), $feeds_num));
		return $feeds;
	}

	protected function introduction() {
		admin_header($this->name);
?>
<h1><?php echo $this->name ?></h1>
<p><?php _e('If a feed reader you use allows you to export your links or subscriptions as OPML you may import them here.'); ?></p>
<p><?php _e('You can import from either <a href="#url_input" class="link">a remote URL</a> or <a href="#file_input" class="link">upload a file</a>.</p>') ?></p>
<form action="feed-import.php" method="post" id="import_form" enctype="multipart/form-data">
	<fieldset id="import">
		<div class="row" id="url_input">
			<label for="url"><?php _e('OPML address (URL)'); ?>:</label>
			<input type="text" name="url" id="url" />
		</div>
		<div class="row" id="file_input">
			<label for="file"><?php _e('Upload an OPML file'); ?>:</label>
			<input type="file" name="file" id="file" />
		</div>
		<p class="buttons"><button type="submit" class="positive"><?php _e('Import'); ?></button></p>
		<input type="hidden" name="step" value="1" />
		<input type="hidden" name="service" value="opml" />
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function () {
		$("#url_input,#file_input").hide();
		$('.link').click(function () {
			$("#url_input,#file_input").hide();
			target = $(this).attr('href');
			$(target).show();
		});
	});
</script>
<?php
		admin_footer();
	}

	protected function error($e) {
	?>
<h1><?php echo $this->name ?></h1>
<p><?php echo $e->getMessage(); ?></p>
<p><?php _e("If you're importing from a remote URL, make sure you typed the URL correctly, and that it points directly to your OPML file. (We can't yet find them automatically!)") ?></p>
<p><?php _e("If you're importing from a local file, make sure you selected the correct file and that you submitted the form correctly. You'll need to click cancel and retry uploading, sorry.") ?></p>
<form action="feed-import.php" method="post">
	<input type="hidden" name="url" id="url" value="<?php echo htmlspecialchars($_POST['url']) ?>" />
	<p class="buttons">
		<button type="submit" class="positive"><?php _e('Try Again'); ?></button>
		<button type="submit" class="negative" name="cancel" value="cancel"><?php _e('Cancel'); ?></button>
	</p>
	<input type="hidden" name="step" value="1" />
	<input type="hidden" name="service" value="opml" />
</form>
<?php
	}

	protected function import() {
		if(!empty($_POST['cancel']) && $_POST['cancel'] == 'cancel') {
			header('HTTP/1.1 302 Found', true, 302);
			header('Location: ' . get_option('baseurl') . 'admin/feed-import.php');
			die();
		}
		if(empty($_POST['url']) && empty($_FILES['file'])) {
			$_POST['step']--;
			$this->dispatch();
			return;
		}

		admin_header($this->name);
		try {
			$opml = '';
			if(!empty($_POST['url'])) {
				$http = new HTTPRequest('', 10, 'Lilina/' . LILINA_CORE_VERSION);
				$opml = $http->get($opml_url);
				$opml = $opml->body;
			}
			if(!empty($_FILES['file'])) {
				$opml = file_get_contents($_FILES['file']['tmp_name']);
			}
			$feeds = $this->import_opml($opml);
			import($feeds);
		}
		catch (Exception $e) {
			$this->error($e);
		}
		admin_footer();
		return;
	}
}

$opml_importer = new OPML_Import(_r('Other (OPML) Importer'));
register_importer('opml', _r('Other (OPML)'), _r('Import feeds from an OPML file'), array(&$opml_importer, 'dispatch'));
?>