<?php
/*
Plugin Name: WP MVC
Plugin URI: http://wordpress.org/extend/plugins/wp-mvc/
Description: Sets up an MVC framework inside of WordPress.
Author: Tom Benner
Version: 1.2
Author URI: 
*/

if (!defined('MVC_PLUGIN_PATH')) {
	define('MVC_PLUGIN_PATH', dirname(__FILE__).'/');
}

if (is_admin()) {

	// Load admin functionality
	
	require_once MVC_PLUGIN_PATH.'core/loaders/mvc_admin_loader.php';
	$loader = new MvcAdminLoader();
	
	add_action('admin_init', array($loader, 'admin_init'));
	add_action('admin_menu', array($loader, 'add_menu_pages'));
	add_action('admin_menu', array($loader, 'add_settings_pages'));
	add_action('plugins_loaded', array($loader, 'add_admin_ajax_routes'));

} 

// Load public functionality
require_once MVC_PLUGIN_PATH.'core/loaders/mvc_public_loader.php';
$loader = new MvcPublicLoader();
$loader->init();

function load_default_query_vars() {
	global $wp;
	foreach(array('mvc_controller','mvc_action','mvc_id') as $qv) {
		$wp->add_query_var( $qv );
	}
}

add_action('wp_loaded', 'load_default_query_vars');
add_action('template_redirect', array($loader, 'template_redirect'));



// Load global functionality

add_action('init', array($loader, 'init'));
add_action('widgets_init', array($loader, 'register_widgets'));
add_filter('post_type_link', array($loader, 'filter_post_link'), 10, 2);
