<?php
/*
Plugin Name: WP MVC
Plugin URI: http://wordpress.org/extend/plugins/wp-mvc/
Description: Sets up an MVC framework inside of WordPress.
Author: Tom Benner
Version: 1.1.2
Author URI: 
*/

if (!defined('MVC_PLUGIN_PATH')) {
	define('MVC_PLUGIN_PATH', dirname(__FILE__).'/');
}

require_once MVC_PLUGIN_PATH.'core/mvc_loader.php';

$mvc_loader = new MvcLoader();

add_action('plugins_loaded', array($mvc_loader, 'plugins_loaded'));

add_action('init', array($mvc_loader, 'init'));

add_action('admin_init', array($mvc_loader, 'admin_init'));

add_action('admin_menu', array($mvc_loader, 'add_menu_pages'));

// Filters for public URLs

add_filter('wp_loaded', array($mvc_loader, 'flush_rewrite_rules'));

add_filter('rewrite_rules_array', array($mvc_loader, 'add_rewrite_rules'));

add_filter('query_vars', array($mvc_loader, 'add_query_vars'));

add_filter('template_redirect', array($mvc_loader, 'template_redirect'));

?>