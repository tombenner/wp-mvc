<?php

class MvcPost extends MvcModel {

    var $table = '{prefix}posts';
    var $primary_key = 'ID';
    var $order = 'post_date DESC';
    var $display_field = 'post_title';
    var $has_many = array(
        'Comment' => array(
            'class' => 'MvcComment',
            'foreign_key' => 'comment_post_ID'
        ),
        'Meta' => array(
            'class' => 'MvcPostMeta',
            'foreign_key' => 'post_id'
        )
    );
    
}

?>