<?php

class AdminEventsController extends MvcAdminController {
    
    var $default_search_joins = array('Speaker', 'Venue');
    var $default_searchable_fields = array('Speaker.first_name', 'Speaker.last_name', 'Venue.name');
    var $default_columns = array(
        'id',
        'date' => array('value_method' => 'admin_column_date'),
        'time' => array('value_method' => 'admin_column_time'),
        'venue' => array('value_method' => 'venue_edit_link'),
        'speaker_names' => 'Speakers'
    );

    public function add() {
        
        $this->set_speakers();
        $this->set_venues();
        $this->create_or_save();
    
    }

    public function edit() {
        
        $this->set_speakers();
        $this->set_venues();
        $this->verify_id_param();
        $this->set_object();
        $this->create_or_save();
    
    }
    
    private function set_speakers() {
    
        $this->load_model('Speaker');
        $speakers = $this->Speaker->find(array('selects' => array('id', 'first_name', 'last_name')));
        $this->set('speakers', $speakers);
    
    }
    
    private function set_venues() {
    
        $this->load_model('Venue');
        $venues = $this->Venue->find(array('selects' => array('id', 'name')));
        $this->set('venues', $venues);
    
    }
    
    public function admin_column_date($object) {
        return empty($object->date) ? null : date('F jS, Y', strtotime($object->date));
    }
    
    public function admin_column_time($object) {
        return empty($object->time) ? null : date('g:ia', strtotime($object->time));
    }
    
    public function venue_edit_link($object) {
        return empty($object->venue) ? null : HtmlHelper::admin_object_link($object->venue, array('action' => 'edit'));
    }
    
}

?>