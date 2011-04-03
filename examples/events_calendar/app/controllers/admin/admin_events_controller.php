<?php

class AdminEventsController extends AdminController {

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
	
}

?>