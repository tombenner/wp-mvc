<?php

class Speaker extends MvcModel {

    var $order = 'Speaker.first_name, Speaker.last_name';
    var $display_field = 'name';
    var $has_many = array('Event');
    var $wp_post = array(
        'post_type' => array(
            'args' => array(
                'menu_icon' => 'dashicons-businessman'
            ),
            'fields' => array(
                'post_content' => '$description'
            )
        )
    );
    var $validate = array(
        // Use a custom regex for the validation
        'first_name' => array(
            'pattern' => '/^[A-Z]/',
            'message' => 'Please enter a capitalized name in the First Name field!'
        ),
        // Use a predefined rule (which includes a generalized message)
        'last_name' => 'not_empty',
        // Use a predefined rule, but allow the field to be empty ('required' => false) and supply a custom message
        'url' => array(
            'rule' => 'url',
            'required' => false,
            'message' => 'Please enter a valid URL in the URL field!'
        )
    );
    
    public function after_find($object) {
        $object->name = trim($object->first_name.' '.$object->last_name);
    }
    
}

?>
