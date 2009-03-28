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

/**
 * Google Reader-to-Lilina importer
 *
 * @package Lilina
*/
class GoogleReader_Import extends OPML_Import {
	/*public function introduction() {
	}*/
}

$greader_importer = new GoogleReader_Import;
register_importer('greader', _r('Google Reader'), _r('Import feeds from Google Reader'), array(&$greader_importer, 'dispatch'));