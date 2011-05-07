<?php

class Speaker extends MvcModel {

	var $order = 'Speaker.first_name, Speaker.last_name';
	var $display_field = 'name';
	var $has_many = array('Event');
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
	
	var $admin_columns = array(
		'id',
		'first_name',
		'last_name',
		'url' => array('value_method' => 'url_link', 'label' => 'URL')
	);
	var $admin_searchable_fields = array('first_name', 'last_name');
	var $admin_pages = array(
		'add',
		'delete',
		'edit',
		'example_page'
	);
	
	public function after_find($object) {
		$object->name = trim($object->first_name.' '.$object->last_name);
	}
	
	public function url_link($object) {
		return empty($object->url) ? null : HtmlHelper::link($object->url, $object->url, array('target' => '_blank'));
	}
	
}

?>