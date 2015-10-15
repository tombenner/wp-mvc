<?php
/*
Plugin Name: Events Calendar Example
Plugin URI: http://wordpress.org/extend/plugins/wp-mvc/
Description: An example application that uses the WP MVC plugin.
Author: Tom Benner
Version: 1.0
Author URI: 
*/

register_activation_hook(__FILE__, 'events_calendar_example_activate');
register_deactivation_hook(__FILE__, 'events_calendar_example_deactivate');

function events_calendar_example_activate() {
    require_once dirname(__FILE__).'/events_calendar_example_loader.php';
    $loader = new EventsCalendarExampleLoader();
    $loader->activate();
}

function events_calendar_example_deactivate() {
    require_once dirname(__FILE__).'/events_calendar_example_loader.php';
    $loader = new EventsCalendarExampleLoader();
    $loader->deactivate();
}

?>