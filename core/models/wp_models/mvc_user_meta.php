<?php

class MvcUserMeta extends MvcModel {

    var $table = '{prefix}usermeta';
    var $primary_key = 'umeta_id';
    var $order = 'meta_key';
    var $display_field = 'meta_key';
    var $belongs_to = array(
        'User' => array(
            'class' => 'MvcUser',
            'foreign_key' => 'user_id'
        )
    );
    
}

?>