<?php

class MvcUser extends MvcModel {

    var $table = '{prefix}users';
    var $primary_key = 'ID';
    var $order = 'user_login';
    var $display_field = 'user_login';
    var $has_many = array(
        'Comment' => array(
            'class' => 'MvcComment',
            'foreign_key' => 'user_id'
        ),
        'Post' => array(
            'class' => 'MvcPost',
            'foreign_key' => 'post_author'
        )
    );
    
}

?>