<?php
/**
 * Contains all the default filters to add to each hook
 * @package Lilina
 */

add_action('admin_init', 'lilina_version_check');

add_action('template_header', 'template_synd_header');

add_action('admin_header', 'update_nag');

add_action('admin_footer', 'lilina_footer_version');

add_filter('init', 'timezone_default');
add_filter('timestamp', 'timezone_apply');
add_filter('the_feed_name', 'get_feed_name', 10, 2);