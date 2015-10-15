<?php

class MvcCommentMeta extends MvcModel {

    var $table = '{prefix}commentmeta';
    var $primary_key = 'meta_id';
    var $order = 'meta_key';
    var $display_field = 'meta_key';
    var $belongs_to = array(
        'Comment' => array(
            'class' => 'MvcComment',
            'foreign_key' => 'comment_id'
        )
    );
    
}

?>