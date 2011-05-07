<?php

class AdminSpeakersController extends MvcAdminController {
	
	function example_page() {
	
		$speakers = $this->Speaker->find(array(
			'selects' => array('id', 'first_name', 'last_name'),
			'order' => 'Speaker.last_name ASC'
		));
		$this->set('speakers', $speakers);
	
	}
	
}

?>