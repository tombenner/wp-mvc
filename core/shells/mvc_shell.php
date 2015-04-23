<?php

class MvcShell {

    protected $core_path = '';
    protected $file_includer = null;

    function __construct($args=array()) {
    
        $this->core_path = MVC_CORE_PATH;
        $this->file_includer = new MvcFileIncluder();
        
        $this->file_includer->require_core_file('console/color.php');
        $this->file_includer->require_core_file('console/table.php');
        
        $this->init($args);
    
    }
    
    /**
     * Empty callback method. This can be overwritten by descendant classes to 
     * add custom functionality during shell initialization
     * 
     * @param type $args 
     */
    protected function init($args=array()) {
    }

    public function main($args) {
        $this->out('To handle commands without any arguments, please define a main() method in the shell.');
    }
    
    public function out($string, $append_new_line = true) {
        echo $string;
        if ($append_new_line) {
            echo "\n";
        }
    }
    
    public function nl($multiplier = 1) {
        echo str_repeat("\n", $multiplier);
    }
    
    public function hr($length = 40) {
        echo str_repeat('-', $length)."\n";
    }
    
    protected function load_helper($helper_name) {
        $helper_name = $helper_name.'Helper';
        $helper_underscore = MvcInflector::underscore($helper_name);
        
        $this->file_includer->require_first_app_file_or_core_file('helpers/'.$helper_underscore.'.php');
        
        if (class_exists($helper_name)) {
            $helper_method_name = str_replace('_helper', '', $helper_underscore);
        
            $this->{$helper_method_name} = new $helper_name();
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

}

?>
