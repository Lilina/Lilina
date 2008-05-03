<?php
/**
 * Contains all the default filters to add to each hook
 * @package Lilina
 */

add_action('init', 'lilina_version_check');

add_action('template_header', 'template_synd_header');

add_action('admin_notices', 'update_nag');

add_action('admin_footer', 'lilina_footer_version');