<?php

class AdminSpeakersController extends MvcAdminController {
    
    var $default_columns = array(
        'id',
        'first_name',
        'last_name',
        'url' => array('value_method' => 'url_link', 'label' => 'URL')
    );
    var $default_searchable_fields = array('first_name', 'last_name');
    
    function example_page() {
    
        $speakers = $this->Speaker->find(array(
            'selects' => array('id', 'first_name', 'last_name'),
            'order' => 'Speaker.last_name ASC'
        ));
        $this->set('speakers', $speakers);
    
    }
    
    public function url_link($object) {
        return empty($object->url) ? null : HtmlHelper::link($object->url, $object->url, array('target' => '_blank'));
    }
    
}

?>