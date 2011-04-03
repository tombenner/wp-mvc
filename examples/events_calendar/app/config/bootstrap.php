<?php

MvcConfiguration::set(array(
	'Debug' => false
));

add_action('mvc_admin_init', 'on_mvc_admin_init');

function on_mvc_admin_init($options) {
	wp_register_style('mvc_admin', mvc_css_url('admin'));
	wp_enqueue_style('mvc_admin');
}

?>