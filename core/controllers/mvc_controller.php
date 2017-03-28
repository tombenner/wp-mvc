<?php

class MvcController {
    
    protected $file_includer = null;
    public $after = null;
    public $before = null;
    public $is_controller = true;
    public $model = null;
    public $name = '';
    public $params = null;
    public $view_rendered = false;
    public $view_vars = array();
    
    function __construct() {
    
        $this->set_meta();
        $this->file_includer = new MvcFileIncluder();
    
    }
    
    public function init() {
    
        $this->load_helper('Html');
        
        $models = MvcModelRegistry::get_models();
        foreach ($models as $model_name => $model) {
            $underscore = MvcInflector::underscore($model_name);
            $tableize = MvcInflector::tableize($model_name);
            // Add dynamicly created methods to HtmlHelper in the form speaker_url($object), speaker_link($object)
            $method = $underscore.'_url';
            $this->html->{$method} = create_function('$object, $options=array()', '
                $defaults = array("controller" => "'.$tableize.'", "action" => "show");
                $options = array_merge($defaults, $options);
                return HtmlHelper::object_url($object, $options);
            ');
            $method = $underscore.'_link';
            $this->html->{$method} = create_function('$object, $options=array()', '
                $defaults = array("controller" => "'.$tableize.'", "action" => "show");
                $options = array_merge($defaults, $options);
                return HtmlHelper::object_link($object, $options);
            ');
        }
        
        if (is_admin()) {
            $this->load_helper('Form');
        }
        
        if (empty($this->model)) {
            $helper_name = class_exists('AppHelper') ? 'AppHelper' : 'MvcHelper';
        } else {
            $model = $this->model->name;
            if (class_exists($model.'Helper')) {
                $helper_name = $model.'Helper';
            } else if (class_exists('AppHelper')) {
                $helper_name = 'AppHelper';
            } else {
                $helper_name = 'MvcHelper';
            }
        }
        $this->helper = new $helper_name();
        
        if (is_string($this->before)) {
            $this->before = array($this->before);
        }
        if (is_string($this->after)) {
            $this->after = array($this->after);
        }
        
    }
    
    public function index() {
    
    }
    
    private function set_meta() {
        $model = get_class($this);
        $model = preg_replace('/Controller$/', '', $model);
        $this->name = MvcInflector::underscore($model);
        $this->views_path = '';
        if (preg_match('/^Admin[A-Z]/', $model)) {
            $this->views_path = 'admin/';
            $model = preg_replace('/^Admin/', '', $model);
        }
        
        $model = MvcInflector::singularize($model);
        $this->views_path .= MvcInflector::tableize($model).'/';
        $this->model_name = $model;
        // To do: remove the necessity of this redundancy
        if (class_exists($model)) {
            $model_instance = new $model();
            $this->model = $model_instance;
            $this->{$model} = $model_instance;
        }
    }
    
    protected function load_helper($helper_name) {
        $helper_name = $helper_name.'Helper';
        $helper_underscore = MvcInflector::underscore($helper_name);
        
        $this->file_includer->require_first_app_file_or_core_file('helpers/'.$helper_underscore.'.php');
        
        if (class_exists($helper_name)) {
            $helper_method_name = str_replace('_helper', '', $helper_underscore);
        
            $this->{$helper_method_name} = new $helper_name();
        
            if ($helper_name == 'FormHelper') {
                $this->{$helper_method_name}->controller = new stdClass();
                $this->{$helper_method_name}->controller->action = $this->action;
                $this->{$helper_method_name}->controller->name = $this->name;
            }
        }
    }
    
    protected function load_model($model_name) {
        $model_underscore = MvcInflector::underscore($model_name);
        
        $this->file_includer->require_first_app_file_or_core_file('models/'.$model_underscore.'.php');
        
        if (class_exists($model_name)) {
            $this->{$model_name} = new $model_name();
        }
    }
    
    protected function load_models($model_names) {
        foreach ($model_names as $model_name) {
            $this->load_model($model_name);
        }
    }
    
    public function set_object() {
        if (!empty($this->model->invalid_data)) {
            if (!empty($this->params['id']) && empty($this->model->invalid_data[$this->model->primary_key])) {
                $this->model->invalid_data[$this->model->primary_key] = $this->params['id'];
            }
            $object = $this->model->new_object($this->model->invalid_data);
        } else if (!empty($this->params['id'])) {
            $object = $this->model->find_by_id($this->params['id']);
        }
        if (!empty($object)) {
            $this->set('object', $object);
            MvcObjectRegistry::add_object($this->model->name, $this->object);
            return true;
        }
        MvcError::warning('Object not found.');
        return false;
    }
    
    public function set($variable_name_or_array, $data=null) {
        if (is_string($variable_name_or_array)) {
            $this->set_view_var($variable_name_or_array, $data);
        } else if (is_array($variable_name_or_array)) {
            foreach ($variable_name_or_array as $key => $value) {
                $this->set_view_var($key, $value);
            }
        }
    }
    
    public function render_view($path, $options=array()) {
    
        // Rendering from within a controller
        if ($this->is_controller) {
            if ($this->is_admin) {
                $layout = empty($options['layout']) ? 'admin' : $options['layout'];
                $layout_directory = 'admin/layouts/';
            } else {
                $layout = empty($options['layout']) ? 'public' : $options['layout'];
                $layout_directory = 'layouts/';
            }
            $this->main_view = $path;
            // We're now entering the view, so $this should no longer be a controller
            $this->is_controller = false;
            $this->set('params', $this->params);
            $this->render_view_with_view_vars($layout_directory.$layout, $options);
            if (!$this->is_admin) {
                die();
            }
        // Rendering from within a view
        } else {
            $this->render_view_from_view($path, $options);
        }
    
    }
    
    public function render_main_view() {
        $this->main_view;
        $this->render_view_with_view_vars($this->main_view);
    }
    
    protected function render_view_with_view_vars($path, $options=array()) {
        
        $view_vars = array(
            'model' => $this->model,
            'views_path' => $this->views_path,
            'helper' => $this->helper
        );
        
        if (!empty($options['locals'])) {
            $view_vars = array_merge($view_vars, $options['locals']);
        }
        
        $this->view_vars = array_merge($this->view_vars, $view_vars);
        $this->include_view($path, $this->view_vars);
        $this->view_rendered = true;
    
    }
    
    protected function render_view_from_view($path, $options=array()) {

        $view_vars = array();
        
        if (strpos($path, '/') === false) {
            $path = $this->name.'/'.$path;
        }
        
        if (isset($options['collection'])) {
            $var_name = empty($options['as']) ? 'object' : $options['as'];
            foreach ($options['collection'] as $object) {
                $view_vars = array();
                $view_vars[$var_name] = $object;
                if (!empty($options['locals'])) {
                    $view_vars = array_merge($view_vars, $options['locals']);
                }
                $this->include_view($path, $view_vars);
            }
            return;
        }
        
        if (!empty($options['locals'])) {
            $view_vars = $options['locals'];
        }
        
        $this->include_view($path, $view_vars);
    
    }
    
    public function render_to_string($path, $options=array()) {
        $defaults = array(
            'bypass_layout' => true,
            'vars' => $this->view_vars
        );
        $is_controller = $this->is_controller;
        $this->is_controller = false;
        $options = array_merge($defaults, $options);
        ob_start();
        $this->include_view($path, $options['vars']);
        $string = ob_get_contents();
        ob_end_clean();
        $this->is_controller = $is_controller;
        return $string;
    }
    
    protected function include_view($path, $view_vars=array()) {
        extract($view_vars);
        $path = preg_replace('/^admin_([^\/]+)/', 'admin/$1', $path);
        $filepath = $this->file_includer->find_first_app_file_or_core_file('views/'.$path.'.php');
        if (!$filepath) {
            $path = preg_replace('/admin\/(?!layouts)([\w_]+)/', 'admin', $path);
            $filepath = $this->file_includer->find_first_app_file_or_core_file('views/'.$path.'.php');
            if (!$filepath) {
                MvcError::warning('View "'.$path.'" not found.');
            }
        }
        require $filepath;
    }
    
    private function set_view_var($key, $value) {
        if ($key == 'object') {
            $this->object = $value;
        }
        $this->view_vars[$key] = $value;
    }
    
    protected function set_flash($type, $message) {
        $this->init_flash();
        $_SESSION['mvc_flash'][$type] = $message;
    }
    
    protected function unset_flash($type) {
        $this->init_flash();
        unset($_SESSION['mvc_flash'][$type]);
    }
    
    protected function get_flash($type) {
        $this->init_flash();
        $message = empty($_SESSION['mvc_flash'][$type]) ? null : $_SESSION['mvc_flash'][$type];
        return $message;
    }
    
    protected function get_all_flashes() {
        $this->init_flash();
        return $_SESSION['mvc_flash'];
    }
    
    public function flash($type, $message=null) {
        if (func_num_args() == 1) {
            $message = $this->get_flash($type);
            $this->unset_flash($type);
            return $message;
        }
        $this->set_flash($type, $message);
    }
    
    public function display_flash() {
        $flashes = $this->get_all_flashes();
        $html = '';
        if (!empty($flashes)) {
            foreach ($flashes as $type => $message) {
                $classes = array();
                $classes[] = $type;
                if ($this->is_admin) {
                    if ($type == 'notice') {
                        $classes[] = 'updated';
                    }
                }
                $html .= '
                    <div id="message" class="'.implode(' ', $classes).'">
                        <p>
                            '.$message.'
                        </p>
                    </div>';
                $this->unset_flash($type);
            }
        }
        echo $html;
    }
    
    private function init_flash() {
        if (!isset($_SESSION['mvc_flash'])) {
            $_SESSION['mvc_flash'] = array();
        }
    }
    
    public function refresh() {
        $location = $this->current_url();
        $this->redirect($location);
    }
    
    public function redirect($location, $status=302) {
        
        // MvcDispatcher::dispatch() doesn't run until after the WP has already begun to print out HTML, unfortunately, so
        // this will almost always be done with JS instead of wp_redirect(). 
        if (headers_sent()) {
            $html = '
                <script type="text/javascript">
                    window.location = "'.$location.'";
                </script>';
            echo $html;
        } else {
            wp_redirect($location, $status);
        }
        
        die();

    }
    
    public function current_url() {
        return $_SERVER['REQUEST_URI'];
    }

}

?>
