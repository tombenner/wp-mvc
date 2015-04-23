<?php

class MvcComment extends MvcModel {

    var $table = '{prefix}comments';
    var $primary_key = 'comment_ID';
    var $order = 'comment_date DESC';
    var $display_field = 'comment_content';
    var $belongs_to = array(
        'Post' => array(
            'class' => 'MvcPost',
            'foreign_key' => 'comment_post_ID'
        )
    );
    var $has_many = array(
        'Meta' => array(
            'class' => 'MvcCommentMeta',
            'foreign_key' => 'comment_id'
        )
    );
    
}

?>