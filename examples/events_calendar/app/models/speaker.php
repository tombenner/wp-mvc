<?php

class Speaker extends AppModel {

	var $order = 'Speaker.first_name, Speaker.last_name';
	var $display_field = 'name';
	var $has_many = array('Event');
	
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