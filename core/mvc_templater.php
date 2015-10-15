<?php

class MvcTemplater {

    private $template_directory = null;
    private $template_vars = array();
    
    public function set_template_directory($directory) {
        $this->template_directory = $directory;
    }
    
    private function set($variable_name_or_array, $value=null) {
        if (is_string($variable_name_or_array)) {
            $this->set_template_var($variable_name_or_array, $data);
        } else if (is_array($variable_name_or_array)) {
            foreach ($variable_name_or_array as $key => $value) {
                $this->set_template_var($key, $value);
            }
        }
    }
    
    private function set_template_var($key, $value) {
        $this->template_vars[$key] = $value;
    }

    public function create($template, $target_path, $vars=array()) {
        if (!empty($vars)) {
            $this->set($vars);
        }
        $template_path = $this->template_directory.$template.'.php';
        if (file_exists($template_path)) {
            extract($this->template_vars);
            ob_start();
            ob_implicit_flush(0);
            include $template_path;
            $content = ob_get_clean();
            $this->create_file($target_path, $content);
        } else {
            MvcError::fatal('A template at "'.$template_path.'" could not be found.');
        }
    }
    
    private function create_file($path, $content) {
        $file = new MvcFile($path);
        $file->write($content);
        
    }

}

?>