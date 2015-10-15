<?php

class Event extends MvcModel {
    
    var $display_field = 'name';
    var $order = 'Event.date ASC';
    var $includes = array('Venue', 'Speaker');
    var $belongs_to = array('Venue');
    var $has_and_belongs_to_many = array(
        'Speaker' => array(
            'join_table' => '{prefix}events_speakers',
            'fields' => array('id', 'first_name', 'last_name')
        )
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
    
}

?>