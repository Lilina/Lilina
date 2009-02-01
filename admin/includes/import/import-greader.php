<?php

require_once(LILINA_PATH . '/admin/includes/import/import-other.php');

class GoogleReader_Import extends OPML_Import {
	/*public function introduction() {
	}*/
}

$greader_importer = new GoogleReader_Import;
register_importer('greader', _r('Google Reader'), _r('Import feeds from Google Reader'), array(&$greader_importer, 'dispatch'));