<?php

MvcConfiguration::set(array(
    'Debug' => false
));

MvcConfiguration::append(array(
    'AdminPages' => array(
        'speakers' => array(
            'add',
            'delete',
            'edit',
            'example_page'
        )
    )
));

add_action('mvc_admin_init', 'events_calendar_on_mvc_admin_init');

function events_calendar_on_mvc_admin_init($options) {
    wp_register_style('mvc_admin', mvc_css_url('events-calendar-example', 'admin'));
    wp_enqueue_style('mvc_admin');
}

?>