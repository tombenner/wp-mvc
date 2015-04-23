<?php

// To do: make this more robust and add error messages
class MvcDirectory {

    private $path = '';

    function __construct($path=null) {
        $this->path = $path;
    }
    
    public function create($path=null, $options=array()) {
        $defaults = array(
            'create_nonexistent_parent_directories' => true
        );
        $options = array_merge($defaults, $options);
        
        if (!$path) {
            return false;
        }
        if ($options['create_nonexistent_parent_directories']) {
            $directories = explode('/', dirname($path));
            for ($i=1; $i<=count($directories); $i++) {
                $directory_path = implode('/', array_slice($directories, 0, $i));
                if (!$this->exists($directory_path)) {
                    $this->create($directory_path);
                }
            }
        }
        
        if (!is_dir($path) && !is_file($path)) {
            mkdir($path);
        }
        
    }
    
    public function delete($path=null) {
    
        if (!$path) {
            return false;
        }
        
        if ($this->exists($path)) {
        
            $objects = scandir($path);
            
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') { 
                    if (filetype($path.'/'.$object) == 'dir') {
                        $this->delete($path.'/'.$object);
                    } else {
                        unlink($path.'/'.$object);
                    }
                }
            }
            
            reset($objects);
            rmdir($path);
        
            return true;
            
        }

    }
    
    public function exists($path=null) {
        return is_dir($path);
    }

}

?>