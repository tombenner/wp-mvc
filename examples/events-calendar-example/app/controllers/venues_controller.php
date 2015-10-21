<?php

class VenuesController extends MvcPublicController {
    
    public function show() {
    
        $object = $this->model->find_by_id($this->params['id'], array(
            'includes' => array('Event')
        ));
        
        if (!empty($object)) {
            $this->set('object', $object);
        }

    }
    
}

?>