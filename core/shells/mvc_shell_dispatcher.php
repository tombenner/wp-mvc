<?php

class MvcShellDispatcher {

    private $dynamic = array();

    function __construct($args) {
    
        $this->file_includer = new MvcFileIncluder();
        
        $this->file_includer->require_core_file('console/color.php');
        
        $this->dispatch($args);
    
    }

    public function __get($name) {
        return isset($this->dynamic[$name]) ? $this->dynamic[$name] : null;
    }

    public function __set($name, $value) {
        $this->dynamic[$name] = $value;
    }

    public function __isset($name) {
        return isset($this->dynamic[$name]);
    }

    public function __unset($name) {
        unset($this->dynamic[$name]);
    }
    
    private function dispatch($args) {
    
        MvcConfiguration::set('ExecutionContext', 'shell');

        echo Console_Color::convert("\n%P%UWelcome to WP MVC Console!%n%n\n\n");
    
        $shell_name = 'help';
        
        if (!empty($args[1])) {
            $shell_name = $args[1];
        }
    
        $shell_title = MvcInflector::camelize($shell_name);
        $shell_name .= '_shell';
        $shell_class_name = MvcInflector::camelize($shell_name);
        
        $shell_path = 'shells/'.$shell_name.'.php';
        $shell_exists = $this->file_includer->include_first_app_file_or_core_file($shell_path);
        
        if (!$shell_exists) {
            echo 'Sorry, a shell named "'.$shell_name.'" couldn\'t be found in any of the MVC plugins.';
            echo "\n";
            echo 'Please make sure a shell class exists in "app/'.$shell_path.'", or execute "./wpmvc" to see a list of available shells.';
            echo "\n";
            die();
        }
        
        $args = array_slice($args, 2);
        
        if (empty($args[0])) {
            $args = array('main');
        }
        
        $shell = new $shell_class_name($args);
        
        $method = $args[0];
        $args = array_slice($args, 1);
        if ($shell_name != 'help_shell') {
            $shell->out(Console_Color::convert("\n%_[Running ".$shell_title."::".$method."]%n"));
        }
        $shell->{$method}($args);
        if ($shell_name != 'help_shell') {
            $shell->out(Console_Color::convert("\n%_[Complete]%n"));
        }
        
    }

}

?>
