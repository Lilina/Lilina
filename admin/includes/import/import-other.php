<?php

class OPML_Import {
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
	 * @param string $opml_url URL to parse feed data from, in the OPML standard.
	 * @return array Associative array containing feed URL, title and category (if applicable)
	 */
	function import_opml($opml_url) {
		if(empty($opml_url)) {
			MessageHandler::add_error(sprintf(_r('No OPML specified')));
			return false;
		}

		require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
		$opml = new SimplePie_File($opml_url);
		$opml = new OPML($opml->body);

		if(!empty($opml->error) || empty($opml->data)) {
			MessageHandler::add_error(sprintf(_r('The OPML file could not be read. The parser said: %s'), $opml->error));
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
		MessageHandler::add(sprintf(__ngettext('Adding %d feed', 'Adding %d feeds', $feeds_num), $feeds_num));
		return $feeds;
	}

/*
admin_header(_r('Import OPML'), 'feeds.php');

$importing = false;

/** Make sure we're actually adding * /
if(isset($_REQUEST['submit'])) {
	if(!isset($_REQUEST['url']))
		MessageHandler::add_error(_r('No URL specified to import OPML from'));
	else
		$importing = import_opml($_REQUEST['url']);
}

	protected function introduction() {
?>
<p><?php _e('If a feed reader you use allows you to export your links or subscriptions as OPML you may import them here.'); ?></p>
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
*/
}

$opml_importer = new OPML_Import;
register_importer('OPML', _r('Other (OPML)'), _r('Import feeds from an OPML file'), array(&$opml_importer, 'dispatch'));
?>