<?php

class MvcPublicController extends MvcController {

    public $is_admin = false;
    
    function __construct() {
        parent::__construct();
        $this->clean_wp_query();
    }
    
    public function index() {
        $this->set_objects();
    }
    
    public function show() {
        $this->set_object();
    }
    
    public function set_objects() {
        $this->process_params_for_search();
        $collection = $this->model->paginate($this->params);
        $this->set('objects', $collection['objects']);
        $this->set_pagination($collection);
    }
    
    public function set_pagination($collection) {
        $params = $this->params;
        unset($params['page']);
        unset($params['conditions']);
        $url = MvcRouter::public_url(array('controller' => $this->name, 'action' => $this->action));
        $this->pagination = array(
            'base' => $url.'%_%',
            'format' => '?page=%#%',
            'total' => $collection['total_pages'],
            'current' => $collection['page'],
            'add_args' => $params
        );
        
        unset($collection['objects']);
        $this->pagination = array_merge($collection, $this->pagination);
    }
    
    public function pagination($options=array()) {
        return paginate_links($this->pagination);
    }
    
    public function set_page_title() {
        add_filter('wp_title', array($this, 'set_wp_title'));
    }
    
    public function after_action($action) {
        $this->set_page_title();
    }
    
    public function set_wp_title($original_title) {
        $separator = ' | ';
        $controller_name = MvcInflector::titleize($this->name);
        $object_name = null;
        $object = null;
        if ($this->action) {
            if ($this->action == 'show' && is_object($this->object)) {
                $object = $this->object;
                if (!empty($this->object->__name)) {
                    $object_name = $this->object->__name;
                }
            }
        }
        $pieces = array(
            $object_name,
            $controller_name
        );
        $pieces = array_filter($pieces);
        $title = implode($separator, $pieces);
        $title = $title.$separator;
        $title_options = apply_filters('mvc_page_title', array(
            'controller' => $controller_name,
            'action' => $this->action,
            'object_name' => $object_name,
            'object' => $object,
            'title' => $title
        ));
        $title = $title_options['title'];
        return $title;
    }
    
    protected function process_params_for_search() {
        $this->params['page'] = empty($this->params['page']) ? 1 : $this->params['page'];
        if (!empty($this->params['q']) && !empty($this->default_searchable_fields)) {
            $this->params['conditions'] = $this->model->get_keyword_conditions($this->default_searchable_fields, $this->params['q']);
            if (!empty($this->default_search_joins)) {
                $this->params['joins'] = $this->default_search_joins;
                $this->params['group'] = $this->model->name.'.'.$this->model->primary_key;
            }
        }
    }
    
    protected function clean_wp_query() {
        global $wp_query;
        $wp_query->is_single = false;
        $wp_query->is_page = false;
        $wp_query->queried_object = null;
        $wp_query->is_home = false;
    }

}

?>