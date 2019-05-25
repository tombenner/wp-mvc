<?php
/*
Plugin Name: Events Calendar Example
Plugin URI: http://wordpress.org/extend/plugins/wp-mvc/
Description: An example application that uses the WP MVC plugin.
Author: Tom Benner
Version: 1.0
Author URI: 
*/

// register activation hook for when the plugin is installed.
register_activation_hook(__FILE__, 'events_calendar_example_activate');
function events_calendar_example_activate($network_wide) {
    require_once dirname(__FILE__).'/events_calendar_example_loader.php';
    $loader = new EventsCalendarExampleLoader();
    $loader->activate($network_wide);
}

// register deactivation hook for when the plugin is uninstalled
register_deactivation_hook(__FILE__, 'events_calendar_example_deactivate');
function events_calendar_example_deactivate($network_wide) {
    require_once dirname(__FILE__).'/events_calendar_example_loader.php';
    $loader = new EventsCalendarExampleLoader();
    $loader->deactivate($network_wide);
}

// register an action handler for when a new blog is created in a multisite environment
add_action('wpmu_new_blog', 'events_calendar_example_on_create_blog');
function events_calendar_example_on_create_blog($blog_id) {
    require_once dirname(__FILE__).'/events_calendar_example_loader.php';
    $loader = new EventsCalendarExampleLoader();
    $loader->activate_blog($blog_id);
}

// register an action handler for when a blog is deleted in a multisite environent
add_action('deleted_blog', 'events_calendar_example_on_delete_blog');
function events_calendar_example_on_delete_blog($blog_id) {
    require_once dirname(__FILE__).'/events_calendar_example_loader.php';
    $loader = new EventsCalendarExampleLoader();
    $loader->deactivate_blog($blog_id);
}

?>
