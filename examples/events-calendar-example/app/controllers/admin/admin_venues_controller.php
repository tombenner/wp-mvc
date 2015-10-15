<?php

class AdminVenuesController extends MvcAdminController {
    
    var $default_columns = array(
        'id',
        'name',
        'url' => 'URL'
    );
    var $default_searchable_fields = array('name');
    
}

?>