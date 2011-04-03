<?php

class Venue extends AppModel {
	
	var $default_order = 'sort_name';
	var $display_field = 'name';
	var $has_many = array('Event');
	
	var $admin_columns = array(
		'id',
		'name',
		'url' => 'URL'
	);
	var $admin_searchable_fields = array('name');
	
}

?>