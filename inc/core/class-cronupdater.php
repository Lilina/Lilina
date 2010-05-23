<?php
/**
 * Cron updater
 */

class CronUpdater extends ItemUpdater {
	protected static $log_file;

	public static function process() {
		header('Content-Type: text/plain; charset=utf-8');
		require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
		$updated = false;
		
		foreach(self::$feeds as $feed) {
			do_action('iu-feed-start', $feed);
			$sp = self::load_feed($feed);
			if($error = $sp->error()) {
				self::log(sprintf(_r('An error occurred with "%2$s": %1$s'), $error, $feed['name']), Errors::get_code('api.itemupdater.itemerror'));
				continue;
			}
			
			$count = 0;
			$items = $sp->get_items();
			foreach($items as $item) {
				$new_item = self::normalise($item, $feed['id']);
				$new_item = apply_filters('item_data_precache', $new_item);
				if(Items::get_instance()->check_item($new_item)) {
					$count++;
					$updated = true;
				}
			}
			do_action('iu-feed-finish', $feed);
		}

		Items::get_instance()->sort_all();
		
		if($updated)
			Items::get_instance()->save_cache();
	}

	public static function log($text, $code) {
		if(empty(self::$log_file))
			self::$log_file = new DataHandler();

		$log = self::$log_file->load('log.json');
		if($log !== null)
			$log = json_decode($log);
		else
			$log = array();

		$log[] = array('code' => $code, 'text' => $text);

		self::$log_file->save('log.json', json_encode($log) );
	}
}