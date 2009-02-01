<?php

require_once(LILINA_PATH . '/admin/includes/import/import-other.php');

class GoogleReader_Import extends OPML_Import {
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
	}
	public function form() {
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
}

$greader_importer = new GoogleReader_Import;
register_importer('greader', _r('Google Reader'), _r('Import feeds from Google Reader'), array(&$greader_importer, 'dispatch'));