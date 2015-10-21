<?php

class MvcPostMeta extends MvcModel {

    var $table = '{prefix}postmeta';
    var $primary_key = 'meta_id';
    var $order = 'meta_key';
    var $display_field = 'meta_key';
    var $belongs_to = array(
        'Post' => array(
            'class' => 'MvcPost',
            'foreign_key' => 'post_id'
        )
    );
    
}

?>