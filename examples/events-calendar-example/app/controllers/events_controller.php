<?php

class EventsController extends MvcPublicController {

    // Overwrite the default index() method to include the 'is_public' => true condition
    public function index() {
    
        $this->params['page'] = empty($this->params['page']) ? 1 : $this->params['page'];
        
        $this->params['conditions'] = array('is_public' => true);
        
        $collection = $this->model->paginate($this->params);
        
        $this->set('objects', $collection['objects']);
        $this->set_pagination($collection);
    
    }
    
    // Event selects only Speaker names and ids by default; to select all fields from Speaker,
    // we'll overwrite the default show() method
    public function show() {
    
        $object = $this->model->find_by_id($this->params['id'], array(
            'includes' => array('Venue', 'Speaker' => array('selects' => 'Speaker.*'))
        ));
        
        if (!empty($object)) {
            $this->set('object', $object);
        }

    }
    
}

?>