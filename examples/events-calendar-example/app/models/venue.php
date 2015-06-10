<?php

class Venue extends MvcModel {
    
    var $default_order = 'sort_name';
    var $display_field = 'name';
    var $has_many = array('Event');
    var $wp_post = array(
        'post_type' => true,
    );
    
    public function after_save($object) {
        $this->update_sort_name($object);
    }
    
    public function update_sort_name($object) {
        $sort_name = $object->name;
        $article = 'The';
        $article_ = $article.' ';
        if (strcasecmp(substr($sort_name, 0, strlen($article_)), $article_) == 0) {
            $sort_name = substr($sort_name, strlen($article_)).', '.$article;
        }
        $this->update($object->__id, array('sort_name' => $sort_name));
    }
    
}

?>
