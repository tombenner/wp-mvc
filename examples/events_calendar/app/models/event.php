<?php

class Event extends AppModel {
	
	var $display_field = 'name';
	var $order = 'Event.date ASC';
	var $includes = array('Venue', 'Speaker');
	var $belongs_to = array('Venue');
	var $has_and_belongs_to_many = array(
		'Speaker' => array(
			'join_table' => 'events_speakers',
			'fields' => array('id', 'first_name', 'last_name')
		)
	);
	
	var $admin_search_joins = array('Speaker', 'Venue');
	var $admin_searchable_fields = array('Speaker.first_name', 'Speaker.last_name', 'Venue.name');
	var $admin_columns = array(
		'id',
		'date' => array('value_method' => 'admin_column_date'),
		'time' => array('value_method' => 'admin_column_time'),
		'venue' => array('value_method' => 'venue_edit_link'),
		'speaker_names' => 'Speakers'
	);
	
	public function after_find($object) {
		if (isset($object->speakers)) {
			$speaker_names = array();
			foreach($object->speakers as $speaker) {
				$speaker_names[] = $speaker->name;
			}
			$object->speaker_names = implode(', ', $speaker_names);
			$object->name = $object->speaker_names;
			if (isset($object->venue)) {
				$object->name .= ' at '.$object->venue->name;
			}
		}
	}
	
	public function admin_column_date($object) {
		return empty($object->date) ? null : date('F jS, Y', strtotime($object->date));
	}
	
	public function admin_column_time($object) {
		return empty($object->time) ? null : date('g:ia', strtotime($object->time));
	}
	
	public function venue_edit_link($object) {
		return empty($object->venue) ? null : HtmlHelper::admin_object_link($object->venue, array('controller' => 'venues', 'action' => 'edit'));
	}
	
}

?>