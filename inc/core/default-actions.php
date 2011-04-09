<?php
/**
 * Contains all the default filters to add to each hook
 * @package Lilina
 */

add_action('admin_init', 'lilina_version_check');
add_action('admin_init', array('Lilina_Updater_Plugins', 'admin_init'));

add_action('template_header', 'template_synd_header');

add_action('admin_header', 'update_nag');

add_action('admin_footer', 'lilina_footer_version');

add_filter('init', 'timezone_default');
add_filter('timestamp', 'timezone_apply');

add_filter('init', array('Templates', 'init_template'));
add_filter('item_data', 'lilina_sanitize_item');